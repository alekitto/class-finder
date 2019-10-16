<?php declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\Reflection;

final class NamespaceFilterIterator extends \FilterIterator
{
    /**
     * @var string[]
     */
    private $namespaces;

    public function __construct(\Iterator $iterator, array $namespaces)
    {
        parent::__construct($iterator);

        $this->namespaces = $namespaces;
    }

    /**
     * {@inheritdoc}
     */
    public function accept(): bool
    {
        $reflectionClass = $this->getInnerIterator()->current();

        foreach ($this->namespaces as $namespace) {
            if ($namespace === $reflectionClass->getNamespaceName() || 0 === \strpos($reflectionClass->getNamespaceName(), $namespace.'\\')) {
                return true;
            }
        }

        return false;
    }
}
