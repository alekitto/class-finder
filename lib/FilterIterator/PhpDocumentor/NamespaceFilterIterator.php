<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\PhpDocumentor;

use FilterIterator;
use Iterator;
use phpDocumentor\Reflection\Element;

use function assert;
use function ltrim;
use function Safe\substr;
use function strpos;
use function strrpos;

final class NamespaceFilterIterator extends FilterIterator
{
    /** @var string[] */
    private array $namespaces;

    /**
     * @param Iterator<Element> $iterator
     * @param string[] $namespaces
     */
    public function __construct(Iterator $iterator, array $namespaces)
    {
        parent::__construct($iterator);

        $this->namespaces = $namespaces;
    }

    public function accept(): bool
    {
        $reflector = $this->getInnerIterator()->current();
        assert($reflector instanceof Element);

        $fqen = (string) $reflector->getFqsen();
        $index = strrpos($fqen, '\\');
        $classNamespace = ltrim($index !== false ? substr($fqen, 0, $index) : $fqen, '\\');

        foreach ($this->namespaces as $namespace) {
            if (strpos($classNamespace, $namespace) === 0) {
                return true;
            }
        }

        return false;
    }
}
