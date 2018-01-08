<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use Composer\Autoload\ClassLoader;
use Kcs\ClassFinder\PathNormalizer;

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
    /**
     * @var ClassLoader
     */
    private $classLoader;

    /**
     * @var array
     */
    private $namespaces;

    /**
     * @var array
     */
    private $dirs;

    public function __construct(ClassLoader $classLoader, ?array $namespaces, ?array $dirs, int $flags = 0)
    {
        $this->classLoader = $classLoader;
        $this->dirs = $dirs;

        if (null !== $namespaces) {
            $namespaces = array_map(function ($ns) {
                return explode('\\', $ns, 2)[0];
            }, $namespaces);

            $this->namespaces = array_unique($namespaces);
        }

        parent::__construct($flags);
    }

    /**
     * {@inheritdoc}
     */
    protected function getGenerator(): \Generator
    {
        yield from $this->searchInClassMap();
        yield from $this->searchInPsrMap();
    }

    /**
     * Searches for class definitions in class map.
     *
     * @return \Generator
     */
    private function searchInClassMap(): \Generator
    {
        foreach ($this->classLoader->getClassMap() as $class => $file) {
            if (! $this->validNamespace($class)) {
                continue;
            }

            if (! $this->validDir($file)) {
                continue;
            }

            yield $class => $file;
        }
    }

    /**
     * Iterates over psr-* maps and yield found classes.
     *
     * NOTE: If the class loader has been generated with ClassMapAuthoritative flag,
     * this method will not yield any element.
     *
     * @return \Generator
     */
    private function searchInPsrMap(): \Generator
    {
        if ($this->classLoader->isClassMapAuthoritative()) {
            // In this case, no psr-* map will be checked when autoloading classes.
            return;
        }

        foreach ($this->traversePrefixes($this->classLoader->getPrefixesPsr4()) as $ns => $dir) {
            yield from (new Psr4Iterator($ns, $dir));
        }

        foreach ($this->traversePrefixes($this->classLoader->getPrefixes()) as $ns => $dir) {
            yield from (new Psr0Iterator($ns, $dir));
        }
    }

    private function traversePrefixes(array $prefixes): \Generator
    {
        foreach ($prefixes as $ns => $dirs) {
            if (! $this->validNamespace($ns)) {
                continue;
            }

            foreach ($dirs as $dir) {
                if (! $this->validDir($dir)) {
                    continue;
                }

                yield $ns => $dir;
            }
        }
    }

    private function validNamespace(string $class): bool
    {
        if (null === $this->namespaces) {
            return true;
        }

        foreach ($this->namespaces as $namespace) {
            if (0 === strpos($class, $namespace)) {
                return true;
            }
        }

        return false;
    }

    private function validDir($path): bool
    {
        if (null === $this->dirs) {
            return true;
        }

        $path = PathNormalizer::resolvePath($path);
        foreach ($this->dirs as $dir) {
            if (0 === strpos($path, $dir)) {
                return true;
            }
        }

        return false;
    }
}
