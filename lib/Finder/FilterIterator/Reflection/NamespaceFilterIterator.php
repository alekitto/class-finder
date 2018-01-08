<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Finder\FilterIterator\Reflection;

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
     * @inheritDoc
     */
    public function accept()
    {
        $reflectionClass = new \ReflectionClass($this->getInnerIterator()->key());

        foreach ($this->namespaces as $namespace) {
            if (strpos($reflectionClass->getNamespaceName(), $namespace) === 0) {
                return true;
            }
        }

        return false;
    }
}
