<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\Reflection;

use FilterIterator;
use Iterator;
use ReflectionClass;

use function strpos;

/**
 * @template-covariant TValue of ReflectionClass
 * @template T of Iterator<class-string, TValue>
 * @template-extends FilterIterator<class-string, TValue, T>
 */
final class NamespaceFilterIterator extends FilterIterator
{
    /**
     * @param T $iterator
     * @param string[] $namespaces
     */
    public function __construct(Iterator $iterator, private array $namespaces)
    {
        parent::__construct($iterator);
    }

    public function accept(): bool
    {
        $reflectionClass = $this->getInnerIterator()->current();

        foreach ($this->namespaces as $namespace) {
            if ($namespace === $reflectionClass->getNamespaceName() || strpos($reflectionClass->getNamespaceName(), $namespace . '\\') === 0) {
                return true;
            }
        }

        return false;
    }
}
