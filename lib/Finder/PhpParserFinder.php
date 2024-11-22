<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Finder;

use CallbackFilterIterator;
use Iterator;
use Kcs\ClassFinder\FilterIterator\PhpParser as Filters;
use Kcs\ClassFinder\Iterator\ClassIterator;
use Kcs\ClassFinder\Iterator\PhpParserIterator;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use RuntimeException;

use function assert;
use function class_exists;

/**
 * Finds classes/namespaces using the registered autoloader by composer.
 */
final class PhpParserFinder implements FinderInterface
{
    use FinderTrait;
    use RecursiveFinderTrait;

    public function __construct(private readonly string $dir)
    {
        if (! class_exists(Stmt\Class_::class)) {
            throw new RuntimeException('nikic/php-parser 4 is not installed. Try execute composer require nikic/php-parser:^4.0');
        }
    }

    /** @return Iterator<class-string, Node> */
    public function getIterator(): Iterator
    {
        $flags = 0;
        if ($this->skipNonInstantiable) {
            $flags |= ClassIterator::SKIP_NON_INSTANTIABLE;
        }

        $pathCallback = $this->pathFilterCallback !== null ? ($this->pathFilterCallback)(...) : null;
        $iterator = new PhpParserIterator($this->dir, $flags, $this->notNamespaces, $pathCallback);
        if (isset($this->fileFinder)) {
            $iterator->setFileFinder($this->fileFinder);
        }

        if ($this->dirs !== null) {
            $iterator->in($this->dirs);
        }

        if ($this->paths !== null) {
            $iterator->path($this->paths);
        }

        if ($this->notPaths !== null) {
            $iterator->notPath($this->notPaths);
        }

        return $this->applyFilters($iterator);
    }

    /**
     * @param Iterator<Stmt\ClassLike> $iterator
     *
     * @return Iterator<Node>
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

        if ($this->filterCallback !== null) {
            $iterator = new CallbackFilterIterator($iterator, function ($current, $key) {
                assert($this->filterCallback !== null);

                return (bool) ($this->filterCallback)($current, $key);
            });
        }

        return $iterator;
    }
}
