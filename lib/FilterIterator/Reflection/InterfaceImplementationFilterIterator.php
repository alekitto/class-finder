<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\Reflection;

use FilterIterator;
use Iterator;
use Reflector;

final class InterfaceImplementationFilterIterator extends FilterIterator
{
    /**
     * @param Iterator<Reflector> $iterator
     * @param string[] $interfaces
     * @phpstan-param class-string[] $interfaces
     */
    public function __construct(Iterator $iterator, private array $interfaces)
    {
        parent::__construct($iterator);
    }

    public function accept(): bool
    {
        $className = $this->getInnerIterator()->key();
        $reflectionClass = $this->getInnerIterator()->current();

        foreach ($this->interfaces as $interface) {
            if ($className !== $interface && $reflectionClass->implementsInterface($interface)) {
                return true;
            }
        }

        return false;
    }
}
