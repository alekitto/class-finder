<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Finder;

use Iterator;
use Kcs\ClassFinder\Iterator\Psr4Iterator;
use Kcs\ClassFinder\PathNormalizer;
use Kcs\ClassFinder\Reflection\ReflectorFactoryInterface;
use Kcs\ClassFinder\Util\BogonFilesFilter;
use Reflector;

use function substr;

use const DIRECTORY_SEPARATOR;

/**
 * Finds classes respecting psr-4 standard.
 */
final class Psr4Finder implements FinderInterface
{
    use ReflectionFilterTrait;
    use RecursiveFinderTrait;

    private string $namespace;
    private string $path;
    private ReflectorFactoryInterface|null $reflectorFactory = null;

    public function __construct(string $namespace, string $path)
    {
        if (substr($namespace, -1) !== '\\') {
            $namespace .= '\\';
        }

        $path = PathNormalizer::resolvePath($path);
        if (substr($path, -1) !== DIRECTORY_SEPARATOR) {
            $path .= DIRECTORY_SEPARATOR;
        }

        $this->namespace = $namespace;
        $this->path = $path;
    }

    public function setReflectorFactory(ReflectorFactoryInterface|null $reflectorFactory): self
    {
        $this->reflectorFactory = $reflectorFactory;

        return $this;
    }

    /** @return Iterator<class-string, Reflector> */
    public function getIterator(): Iterator
    {
        $pathFilterCallback = $this->pathFilterCallback !== null ? ($this->pathFilterCallback)(...) : null;
        if ($this->skipBogonClasses) {
            $pathFilterCallback = BogonFilesFilter::getFileFilterFn($pathFilterCallback);
        }

        $iterator = new Psr4Iterator(
            $this->namespace,
            $this->path,
            $this->reflectorFactory,
            pathCallback: $pathFilterCallback,
        );

        if (isset($this->fileFinder)) {
            $iterator->setFileFinder($this->fileFinder);
        }

        return $this->applyFilters($iterator);
    }
}
