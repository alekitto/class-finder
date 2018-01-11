<?php declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\PhpDocumentor;

use phpDocumentor\Reflection\BaseReflector;

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
        /** @var BaseReflector $reflector */
        $reflector = $this->getInnerIterator()->current();
        $classNamespace = ltrim($reflector->getNamespace(), '\\');

        foreach ($this->namespaces as $namespace) {
            if (0 === strpos($classNamespace, $namespace)) {
                return true;
            }
        }

        return false;
    }
}
