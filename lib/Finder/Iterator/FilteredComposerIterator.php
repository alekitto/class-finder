<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Finder\Iterator;

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
        $this->namespaces = $namespaces;
        $this->dirs = $dirs;

        parent::__construct($flags);
    }

    /**
     * @inheritdoc
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

        foreach ($this->classLoader->getPrefixesPsr4() as $ns => $dirs) {
            if (! $this->validNamespace($ns)) {
                continue;
            }

            foreach ($dirs as $dir) {
                if (! $this->validDir($dir)) {
                    continue;
                }

                yield from (new Psr4Iterator($ns, $dir));
            }
        }

        foreach ($this->classLoader->getPrefixes() as $ns => $dirs) {
            if (! $this->validNamespace($ns)) {
                continue;
            }

            foreach ($dirs as $dir) {
                if (! $this->validDir($dir)) {
                    continue;
                }

                yield from (new Psr0Iterator($ns, $dir));
            }
        }
    }

    private function validNamespace(string $class): bool
    {
        if (null === $this->namespaces) {
            return true;
        }

        foreach ($this->namespaces as $namespace) {
            if (strpos($class, $namespace) === 0) {
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
            if (strpos($dir, $path) === 0) {
                return true;
            }
        }

        return false;
    }
}
