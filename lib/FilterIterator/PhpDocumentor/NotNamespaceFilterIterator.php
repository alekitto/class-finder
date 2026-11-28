<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\PhpDocumentor;

use FilterIterator;
use Iterator;
use phpDocumentor\Reflection\Element;

use function array_all;
use function ltrim;
use function str_starts_with;
use function strrpos;
use function substr;

/**
 * @template TKey
 * @extends FilterIterator<TKey, Element, Iterator<TKey, Element>>
 */
final class NotNamespaceFilterIterator extends FilterIterator
{
    /**
     * @param Iterator<Element> $iterator
     * @param string[] $namespaces
     */
    public function __construct(Iterator $iterator, private readonly array $namespaces)
    {
        parent::__construct($iterator);
    }

    public function accept(): bool
    {
        $reflector = $this->getInnerIterator()->current();
        $fqen = (string) $reflector->getFqsen();
        $index = strrpos($fqen, '\\');
        $classNamespace = ltrim($index !== false ? substr($fqen, 0, $index) : $fqen, '\\');

        return array_all(
            $this->namespaces,
            static fn (string $namespace) => $classNamespace !== $namespace && ! str_starts_with($classNamespace, $namespace . '\\'),
        );
    }
}
