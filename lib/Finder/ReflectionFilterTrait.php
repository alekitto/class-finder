<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Finder;

use CallbackFilterIterator;
use Iterator;
use Kcs\ClassFinder\FilterIterator\Reflection as Filters;
use ReflectionClass;

use function assert;

trait ReflectionFilterTrait
{
    use FinderTrait;

    /**
     * @param T $iterator
     *
     * @return Iterator<class-string, ReflectionClass>
     *
     * @template T of Iterator<class-string, ReflectionClass>
     */
    private function applyFilters(Iterator $iterator): Iterator
    {
        if ($this->namespaces) {
            $iterator = new Filters\NamespaceFilterIterator($iterator, $this->namespaces);
        }

        if ($this->dirs) {
            $iterator = new Filters\DirectoryFilterIterator($iterator, $this->dirs);
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

        if ($this->paths || $this->notPaths) {
            $iterator = new Filters\PathFilterIterator($iterator, $this->paths ?? [], $this->notPaths ?? []);
        }

        return $iterator;
    }
}
