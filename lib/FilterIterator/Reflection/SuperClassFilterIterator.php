<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\Reflection;

use FilterIterator;
use Iterator;
use ReflectionClass;

/**
 * @template-covariant TValue of ReflectionClass
 * @template T of Iterator<class-string, TValue>
 * @template-extends FilterIterator<class-string, TValue, T>
 */
final class SuperClassFilterIterator extends FilterIterator
{
    /**
     * @param T $iterator
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
