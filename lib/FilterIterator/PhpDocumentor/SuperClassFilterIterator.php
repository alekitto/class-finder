<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\PhpDocumentor;

use FilterIterator;
use Iterator;
use phpDocumentor\Reflection\Element;
use phpDocumentor\Reflection\Php\Class_;

use function ltrim;

final class SuperClassFilterIterator extends FilterIterator
{
    private string $superClass;

    /**
     * @param Iterator<Element> $iterator
     *
     * @phpstan-param class-string $superClass
     */
    public function __construct(Iterator $iterator, string $superClass)
    {
        parent::__construct($iterator);

        $this->superClass = $superClass;
    }

    public function accept(): bool
    {
        $reflector = $this->getInnerIterator()->current();

        return $reflector instanceof Class_ && ltrim((string) $reflector->getParent(), '\\') === $this->superClass;
    }
}
