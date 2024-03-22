<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Finder;

use Kcs\ClassFinder\Iterator\RecursiveIterator;
use Kcs\ClassFinder\PathNormalizer;
use Kcs\ClassFinder\Util\BogonFilesFilter;
use ReflectionClass;
use Traversable;

final class RecursiveFinder implements FinderInterface
{
    use ReflectionFilterTrait;

    private string $path;

    public function __construct(string $path)
    {
        $path = PathNormalizer::resolvePath($path);
        $this->path = $path;
    }

    /** @return Traversable<class-string, ReflectionClass> */
    public function getIterator(): Traversable
    {
        $pathFilterCallback = $this->pathFilterCallback !== null ? ($this->pathFilterCallback)(...) : null;
        if ($this->skipBogonClasses) {
            $pathFilterCallback = BogonFilesFilter::getFileFilterFn($pathFilterCallback);
        }

        return $this->applyFilters(new RecursiveIterator(
            $this->path,
            pathCallback: $pathFilterCallback,
        ));
    }
}
