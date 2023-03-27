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

final class NotNamespaceFilterIterator extends FilterIterator
{
    /**
     * @param Iterator<Element> $iterator
     * @param string[] $namespaces
     */
    public function __construct(Iterator $iterator, private array $namespaces)
    {
        parent::__construct($iterator);
    }

    public function accept(): bool
    {
        $reflector = $this->getInnerIterator()->current();
        assert($reflector instanceof Element);

        $fqen = (string) $reflector->getFqsen();
        $index = strrpos($fqen, '\\');
        $classNamespace = ltrim($index !== false ? substr($fqen, 0, $index) : $fqen, '\\');

        foreach ($this->namespaces as $namespace) {
            if ($classNamespace === $namespace || strpos($classNamespace, $namespace . '\\') === 0) {
                return false;
            }
        }

        return true;
    }
}
