<?php declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\Reflection;

final class SuperClassFilterIterator extends \FilterIterator
{
    /**
     * @var string
     */
    private $superClass;

    public function __construct(\Iterator $iterator, string $superClass)
    {
        parent::__construct($iterator);

        $this->superClass = $superClass;
    }

    /**
     * {@inheritdoc}
     */
    public function accept()
    {
        $reflectionClass = new \ReflectionClass($this->getInnerIterator()->key());

        return $reflectionClass->isSubclassOf($this->superClass);
    }
}
