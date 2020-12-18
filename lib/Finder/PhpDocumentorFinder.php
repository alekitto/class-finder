<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Finder;

use Iterator;
use Kcs\ClassFinder\Iterator\PhpDocumentorIterator;
use phpDocumentor\Reflection\Element;
use phpDocumentor\Reflection\Php\Class_;
use RuntimeException;

use function class_exists;

/**
 * Finds classes/namespaces using the registered autoloader by composer.
 */
final class PhpDocumentorFinder implements FinderInterface
{
    use PhpDocumentorFilterTrait;

    private string $dir;

    public function __construct(string $dir)
    {
        if (! class_exists(Class_::class)) {
            throw new RuntimeException('phpdocumentor/reflection 4 is not installed. Try execute composer require phpdocumentor/reflection:^4.0');
        }

        $this->dir = $dir;
    }

    /**
     * @return Iterator<Element>
     */
    public function getIterator(): Iterator
    {
        $iterator = new PhpDocumentorIterator($this->dir);

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
}
