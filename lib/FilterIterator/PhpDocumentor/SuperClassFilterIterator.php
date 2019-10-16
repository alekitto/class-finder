<?php declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\PhpDocumentor;

use phpDocumentor\Reflection\ClassReflector;

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
    public function accept(): bool
    {
        $reflector = $this->getInnerIterator()->current();

        return $reflector instanceof ClassReflector && ltrim($reflector->getParentClass(), '\\') === $this->superClass;
    }
}
