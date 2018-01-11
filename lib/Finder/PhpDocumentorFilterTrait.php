<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Finder;

use Kcs\ClassFinder\FilterIterator\PhpDocumentor as Filters;

trait PhpDocumentorFilterTrait
{
    use FinderTrait;

    private function applyFilters(\Iterator $iterator): \Iterator
    {
        if ($this->namespaces) {
            $iterator = new Filters\NamespaceFilterIterator($iterator, $this->namespaces);
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

        if ($this->callback) {
            $iterator = new \CallbackFilterIterator($iterator, function ($current, $key) {
                return (bool) ($this->callback)($current, $key);
            });
        }

        return $iterator;
    }
}
