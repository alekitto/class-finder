<?php declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\Reflection;

final class InterfaceImplementationFilterIterator extends \FilterIterator
{
    /**
     * @var string[]
     */
    private $interfaces;

    public function __construct(\Iterator $iterator, array $interfaces)
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

        $reflectionClass = new \ReflectionClass($className);
        foreach ($this->interfaces as $interface) {
            if ($className !== $interface && $reflectionClass->implementsInterface($interface)) {
                return true;
            }
        }

        return false;
    }
}
