<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\Reflection;

use FilterIterator;
use Iterator;
use Reflector;

final class SuperClassFilterIterator extends FilterIterator
{
    private string $superClass;

    /**
     * @param Iterator<Reflector> $iterator
     *
     * @phpstan-param class-string $superClass
     */
    public function __construct(Iterator $iterator, string $superClass)
    {
        parent::__construct($iterator);

        $this->superClass = $superClass;
    }

    public function accept(): bool
    {
        $reflectionClass = $this->getInnerIterator()->current();

        return $reflectionClass->isSubclassOf($this->superClass);
    }
}
