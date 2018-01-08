<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Finder\FilterIterator\Reflection;

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
     * @inheritDoc
     */
    public function accept()
    {
        $reflectionClass = new \ReflectionClass($this->getInnerIterator()->key());
        foreach ($this->interfaces as $interface) {
            if ($reflectionClass->implementsInterface($interface)) {
                return true;
            }
        }

        return false;
    }
}
