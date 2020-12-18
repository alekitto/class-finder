<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\Reflection;

use FilterIterator;
use Iterator;
use Reflector;

final class InterfaceImplementationFilterIterator extends FilterIterator
{
    /** @var string[] */
    private array $interfaces;

    /**
     * @param Iterator<Reflector> $iterator
     * @param string[] $interfaces
     *
     * @phpstan-param class-string[] $interfaces
     */
    public function __construct(Iterator $iterator, array $interfaces)
    {
        parent::__construct($iterator);

        $this->interfaces = $interfaces;
    }

    /**
     * {@inheritdoc}
     */
    public function accept()
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
