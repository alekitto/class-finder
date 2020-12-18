<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\Reflection;

use FilterIterator;
use Iterator;
use Reflector;

use function strpos;

final class NamespaceFilterIterator extends FilterIterator
{
    /** @var string[] */
    private array $namespaces;

    /**
     * @param Iterator<Reflector> $iterator
     * @param string[] $namespaces
     */
    public function __construct(Iterator $iterator, array $namespaces)
    {
        parent::__construct($iterator);

        $this->namespaces = $namespaces;
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
