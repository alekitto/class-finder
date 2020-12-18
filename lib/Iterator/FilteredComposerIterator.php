<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use Composer\Autoload\ClassLoader;
use Generator;
use Kcs\ClassFinder\PathNormalizer;
use ReflectionClass;

use function array_map;
use function array_unique;
use function explode;
use function strpos;

/**
 * Iterates over classes defined in a composer-generated ClassLoader
 * and do some dirty filtering.
 *
 * This class is for internal use.
 * Do not use it directly: its filters are not precise enough, and
 * would lead to bad results.
 *
 * @internal
 */
final class FilteredComposerIterator extends ClassIterator
{
    private ClassLoader $classLoader;

    /** @var string[]|null */
    private ?array $namespaces = null;

    /** @var string[]|null */
    private ?array $dirs = null;

    /**
     * @param string[]|null $namespaces
     * @param string[]|null $dirs
     */
    public function __construct(ClassLoader $classLoader, ?array $namespaces, ?array $dirs, int $flags = 0)
    {
        $this->classLoader = $classLoader;
        $this->dirs = $dirs !== null ? array_map(PathNormalizer::class . '::resolvePath', $dirs) : $dirs;

        if ($namespaces !== null) {
            $namespaces = array_map(static function ($ns) {
                return explode('\\', $ns, 2)[0];
            }, $namespaces);

            $this->namespaces = array_unique($namespaces);
        }

        parent::__construct($flags);
    }

    protected function getGenerator(): Generator
    {
        yield from $this->searchInClassMap();
        yield from $this->searchInPsrMap();
    }

    /**
     * Searches for class definitions in class map.
     */
    private function searchInClassMap(): Generator
    {
        foreach ($this->classLoader->getClassMap() as $class => $file) {
            if (! $this->validNamespace($class)) {
                continue;
            }

            if (! $this->validDir(PathNormalizer::resolvePath($file))) {
                continue;
            }

            yield $class => new ReflectionClass($class);
        }
    }

    /**
     * Iterates over psr-* maps and yield found classes.
     *
     * NOTE: If the class loader has been generated with ClassMapAuthoritative flag,
     * this method will not yield any element.
     */
    private function searchInPsrMap(): Generator
    {
        if ($this->classLoader->isClassMapAuthoritative()) {
            // In this case, no psr-* map will be checked when autoloading classes.
            return;
        }

        foreach ($this->traversePrefixes($this->classLoader->getPrefixesPsr4()) as $ns => $dir) {
            yield from new Psr4Iterator($ns, $dir, 0, $this->classLoader->getClassMap());
        }

        foreach ($this->traversePrefixes($this->classLoader->getPrefixes()) as $ns => $dir) {
            yield from new Psr0Iterator($ns, $dir, 0, $this->classLoader->getClassMap());
        }
    }

    /**
     * @param array<string, string[]> $prefixes
     */
    private function traversePrefixes(array $prefixes): Generator
    {
        foreach ($prefixes as $ns => $dirs) {
            if (! $this->validNamespace($ns)) {
                continue;
            }

            foreach ($dirs as $dir) {
                $dir = PathNormalizer::resolvePath($dir);
                if (! $this->validDir($dir)) {
                    continue;
                }

                yield $ns => $dir;
            }
        }
    }

    private function validNamespace(string $class): bool
    {
        if ($this->namespaces === null) {
            return true;
        }

        foreach ($this->namespaces as $namespace) {
            if (strpos($class, $namespace) === 0) {
                return true;
            }
        }

        return false;
    }

    private function validDir(string $path): bool
    {
        if ($this->dirs === null) {
            return true;
        }

        foreach ($this->dirs as $dir) {
            // Check for intersection of required path and evaluated dir
            if (strpos($path, $dir) !== false || strpos($dir, $path) !== false) {
                return true;
            }
        }

        return false;
    }
}
