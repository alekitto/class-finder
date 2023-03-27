<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\Reflection;

use FilterIterator;
use Iterator;
use Reflector;

use function count;

final class AttributeFilterIterator extends FilterIterator
{
    /**
     * @param Iterator<Reflector> $iterator
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
