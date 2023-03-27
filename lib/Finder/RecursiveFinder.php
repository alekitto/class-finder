<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Finder;

use Kcs\ClassFinder\Iterator\RecursiveIterator;
use Kcs\ClassFinder\PathNormalizer;
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
        return $this->applyFilters(new RecursiveIterator($this->path));
    }
}
