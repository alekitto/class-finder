<?php declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\PhpDocumentor;

use phpDocumentor\Reflection\BaseReflector;
use phpDocumentor\Reflection\ClassReflector;

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
        /** @var BaseReflector $reflector */
        $reflector = $this->getInnerIterator()->current();
        if (! $reflector instanceof ClassReflector) {
            return false;
        }

        $interfaces = array_map(function (string $interface): string {
            return ltrim($interface, '\\');
        }, $reflector->getInterfaces());

        foreach ($this->interfaces as $interface) {
            if (in_array($interface, $interfaces)) {
                return true;
            }
        }

        return false;
    }
}
