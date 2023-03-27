<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\Reflection;

use FilterIterator;
use Iterator;
use ReflectionClass;

use function count;

/**
 * @template-covariant  TValue of ReflectionClass
 * @template T of Iterator<class-string, TValue>
 * @template-extends FilterIterator<class-string, TValue, T>
 */
final class AttributeFilterIterator extends FilterIterator
{
    /**
     * @param T $iterator
     * @phpstan-param class-string $attribute
     */
    public function __construct(Iterator $iterator, private string $attribute)
    {
        parent::__construct($iterator);
    }

    public function accept(): bool
    {
        $reflectionClass = $this->getInnerIterator()->current();
        if ($reflectionClass->isInternal()) {
            return false;
        }

        return count($reflectionClass->getAttributes($this->attribute)) > 0;
    }
}
