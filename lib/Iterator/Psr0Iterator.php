<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use Closure;
use Generator;
use Kcs\ClassFinder\PathNormalizer;
use Kcs\ClassFinder\Util\ErrorHandler;
use ReflectionClass;
use Throwable;

use function array_map;
use function defined;
use function in_array;
use function ltrim;
use function Safe\preg_match;
use function Safe\substr;
use function str_replace;
use function strlen;
use function strpos;

final class Psr0Iterator extends ClassIterator
{
    use PsrIteratorTrait;

    private string $namespace;
    private int $pathLen;

    /** @var string[] */
    private array $classMap;

    /**
     * @param array<string, mixed> $classMap
     */
    public function __construct(string $namespace, string $path, int $flags = 0, array $classMap = [])
    {
        $this->namespace = $namespace;
        $this->path = PathNormalizer::resolvePath($path);
        $this->pathLen = strlen($this->path);
        $this->classMap = array_map(PathNormalizer::class . '::resolvePath', $classMap);

        parent::__construct($flags);
    }

    protected function getGenerator(): Generator
    {
        $pattern = defined('HHVM_VERSION') ? '/\\.(php|hh)$/i' : '/\\.php$/i';
        $include = Closure::bind(static function (string $path) {
            include_once $path;
        }, null, null);

        foreach ($this->search() as $path => $info) {
            if (! preg_match($pattern, $path, $m) || ! $info->isReadable()) {
                continue;
            }

            if (in_array($path, $this->classMap, true)) {
                continue;
            }

            /** @phpstan-var class-string $class */
            $class = ltrim(str_replace('/', '\\', substr($path, $this->pathLen, -strlen($m[0]))), '\\');
            if (strpos($class, $this->namespace) !== 0) {
                continue;
            }

            if (! preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*+(?:\\\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*+)*+$/', $class)) {
                continue;
            }

            // Due to composer bug #6987 and the refuse of think about a proper
            // solution, we are forced to include the file here and check if class
            // exists with autoload flag disabled (see method exists).

            ErrorHandler::register();
            try {
                $include($path);
            } catch (Throwable $e) { /** @phpstan-ignore-line */
                continue;
            } finally {
                ErrorHandler::unregister();
            }

            if (! $this->exists($class)) {
                continue;
            }

            yield $class => new ReflectionClass($class);
        }
    }
}
