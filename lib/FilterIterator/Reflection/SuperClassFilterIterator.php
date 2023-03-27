<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\Reflection;

use FilterIterator;
use Iterator;
use Reflector;

final class SuperClassFilterIterator extends FilterIterator
{
    /**
     * @param Iterator<Reflector> $iterator
     * @phpstan-param class-string $superClass
     */
    public function __construct(Iterator $iterator, private string $superClass)
    {
        parent::__construct($iterator);
    }

    public function accept(): bool
    {
        $reflectionClass = $this->getInnerIterator()->current();

        return $reflectionClass->isSubclassOf($this->superClass);
    }
}
