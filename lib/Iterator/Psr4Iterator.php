<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use Closure;
use Generator;
use Kcs\ClassFinder\PathNormalizer;
use Kcs\ClassFinder\Reflection\NativeReflectorFactory;
use Kcs\ClassFinder\Reflection\ReflectorFactoryInterface;
use Kcs\ClassFinder\Util\ErrorHandler;
use Throwable;

use function array_map;
use function assert;
use function class_exists;
use function defined;
use function in_array;
use function ltrim;
use function Safe\preg_match;
use function str_replace;
use function strlen;
use function substr;

final class Psr4Iterator extends ClassIterator
{
    use PsrIteratorTrait;

    private int $prefixLen;
    private ReflectorFactoryInterface $reflectorFactory;

    /** @var array<string, mixed> */
    private array $classMap;

    /**
     * @param array<string, mixed> $classMap
     * @param string[] $excludeNamespaces
     */
    public function __construct(
        private readonly string $namespace,
        string $path,
        ReflectorFactoryInterface|null $reflectorFactory = null,
        int $flags = 0,
        array $classMap = [],
        array|null $excludeNamespaces = null,
        Closure|null $pathCallback = null,
    ) {
        $this->path = PathNormalizer::resolvePath($path);
        $this->reflectorFactory = $reflectorFactory ?? new NativeReflectorFactory();
        $this->prefixLen = strlen($this->path);
        $this->classMap = array_map(PathNormalizer::class . '::resolvePath', $classMap);

        parent::__construct($flags, $excludeNamespaces, $pathCallback);
    }

    protected function getGenerator(): Generator
    {
        $pattern = defined('HHVM_VERSION') ? '/\\.(php|hh)$/i' : '/\\.php$/i';
        $include = Closure::bind(
            $this->flags & self::USE_AUTOLOADING ?
                static function (string $path, string $class): void {
                    class_exists($class, true);
                } : static function (string $path): void {
                    include_once $path;
                },
            null,
            null,
        );

        assert($include instanceof Closure);

        foreach ($this->search() as $path => $info) {
            $path = PathNormalizer::resolvePath($path);
            if (! preg_match($pattern, $path, $m) || ! $info->isReadable()) {
                continue;
            }

            if (in_array($path, $this->classMap, true)) {
                continue;
            }

            if ($this->pathCallback && ! ($this->pathCallback)(PathNormalizer::normalize($path))) {
                continue;
            }

            /** @phpstan-var class-string $class */
            $class = $this->namespace . ltrim(str_replace('/', '\\', substr($path, $this->prefixLen, -strlen($m[0]))), '\\');
            if (! $this->validNamespace($class)) {
                continue;
            }

            // Due to composer bug #6987 and the refuse of think about a proper
            // solution, we are forced to include the file here and check if class
            // exists with autoload flag disabled (see method exists).

            ErrorHandler::register();
            try {
                $include($path, $class);
            } catch (Throwable) { /** @phpstan-ignore-line */
                continue;
            } finally {
                ErrorHandler::unregister();
            }

            if (! $this->exists($class)) {
                continue;
            }

            yield $class => $this->reflectorFactory->reflect($class);
        }
    }
}
