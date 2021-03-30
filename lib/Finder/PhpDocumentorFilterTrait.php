<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Finder;

use CallbackFilterIterator;
use Iterator;
use Kcs\ClassFinder\FilterIterator\PhpDocumentor as Filters;
use phpDocumentor\Reflection\Element;

use function assert;

trait PhpDocumentorFilterTrait
{
    use FinderTrait;

    /**
     * @param Iterator<Element> $iterator
     *
     * @return Iterator<Element>
     */
    private function applyFilters(Iterator $iterator): Iterator
    {
        if ($this->namespaces) {
            $iterator = new Filters\NamespaceFilterIterator($iterator, $this->namespaces);
        }

        if ($this->notNamespaces) {
            $iterator = new Filters\NotNamespaceFilterIterator($iterator, $this->notNamespaces);
        }

        if ($this->implements) {
            $iterator = new Filters\InterfaceImplementationFilterIterator($iterator, $this->implements);
        }

        if ($this->extends) {
            $iterator = new Filters\SuperClassFilterIterator($iterator, $this->extends);
        }

        if ($this->annotation) {
            $iterator = new Filters\AnnotationFilterIterator($iterator, $this->annotation);
        }

        if ($this->attribute) {
            $iterator = new Filters\AttributeFilterIterator($iterator, $this->attribute);
        }

        if ($this->callback !== null) {
            $iterator = new CallbackFilterIterator($iterator, function ($current, $key) {
                assert($this->callback !== null);

                return (bool) ($this->callback)($current, $key);
            });
        }

        return $iterator;
    }
}
