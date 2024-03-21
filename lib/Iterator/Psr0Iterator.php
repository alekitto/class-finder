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
use function strpos;
use function substr;

final class Psr0Iterator extends ClassIterator
{
    use PsrIteratorTrait;

    private int $pathLen;
    private ReflectorFactoryInterface $reflectorFactory;

    /** @var string[] */
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
        private readonly array|null $excludeNamespaces = null,
        Closure|null $pathCallback = null,
    ) {
        $this->path = PathNormalizer::resolvePath($path);
        $this->reflectorFactory = $reflectorFactory ?? new NativeReflectorFactory();
        $this->pathLen = strlen($this->path);
        $this->classMap = array_map(PathNormalizer::class . '::resolvePath', $classMap);

        parent::__construct($flags, $pathCallback);
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
            if (! preg_match($pattern, $path, $m) || ! $info->isReadable()) {
                continue;
            }

            if (in_array($path, $this->classMap, true)) {
                continue;
            }

            if ($this->pathCallback && ! ($this->pathCallback)($path)) {
                continue;
            }

            /** @phpstan-var class-string $class */
            $class = ltrim(str_replace('/', '\\', substr($path, $this->pathLen, -strlen($m[0]))), '\\');
            if (strpos($class, $this->namespace) !== 0) {
                continue;
            }

            if ($this->excludeNamespaces !== null) {
                foreach ($this->excludeNamespaces as $namespace) {
                    if (strpos($class, $namespace) === 0) {
                        continue 2;
                    }
                }
            }

            if (! preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*+(?:\\\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*+)*+$/', $class)) {
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
