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
    public function accept()
    {
        $reflectionClass = new \ReflectionClass($this->getInnerIterator()->key());

        foreach ($this->namespaces as $namespace) {
            if (0 === strpos($reflectionClass->getNamespaceName(), $namespace)) {
                return true;
            }
        }

        return false;
    }
}
