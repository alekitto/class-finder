<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Finder;

use Iterator;
use Kcs\ClassFinder\Iterator\ClassIterator;
use Kcs\ClassFinder\Iterator\ClassMapIterator;
use Kcs\ClassFinder\Reflection\ReflectorFactoryInterface;
use Kcs\ClassFinder\Util\BogonFilesFilter;
use Reflector;

/**
 * Finds classes into a classmap.
 */
final class ClassMapFinder implements FinderInterface
{
    use ReflectionFilterTrait;

    private ReflectorFactoryInterface|null $reflectorFactory = null;

    /** @param array<class-string, string> $classMap */
    public function __construct(private readonly array $classMap)
    {
    }

    public function setReflectorFactory(ReflectorFactoryInterface|null $reflectorFactory): self
    {
        $this->reflectorFactory = $reflectorFactory;

        return $this;
    }

    /** @return Iterator<class-string, Reflector> */
    public function getIterator(): Iterator
    {
        $flags = 0;
        if ($this->skipNonInstantiable) {
            $flags |= ClassIterator::SKIP_NON_INSTANTIABLE;
        }

        $pathFilterCallback = $this->pathFilterCallback !== null ? ($this->pathFilterCallback)(...) : null;
        if ($this->skipBogonClasses) {
            $pathFilterCallback = BogonFilesFilter::getFileFilterFn($pathFilterCallback);
        }

        $iterator = new ClassMapIterator(
            $this->classMap,
            $this->reflectorFactory,
            $flags,
            $this->notNamespaces,
            pathCallback: $pathFilterCallback,
        );

        return $this->applyFilters($iterator);
    }
}
