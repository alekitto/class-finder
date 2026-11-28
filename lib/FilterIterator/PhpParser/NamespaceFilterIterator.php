<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\PhpParser;

use FilterIterator;
use Iterator;
use PhpParser\Node;
use PhpParser\Node\Stmt;

use function assert;
use function ltrim;
use function str_starts_with;
use function strrpos;
use function substr;

/** @extends FilterIterator<class-string, Node, Iterator<class-string, Node>> */
final class NamespaceFilterIterator extends FilterIterator
{
    /**
     * @param Iterator<class-string, Node> $iterator
     * @param string[] $namespaces
     */
    public function __construct(Iterator $iterator, private readonly array $namespaces)
    {
        parent::__construct($iterator);
    }

    public function accept(): bool
    {
        $reflector = $this->getInnerIterator()->current();
        assert($reflector instanceof Stmt\ClassLike);

        $fqen = ltrim((string) $reflector->namespacedName, '\\');
        $index = strrpos($fqen, '\\');
        $classNamespace = ltrim($index !== false ? substr($fqen, 0, $index) : $fqen, '\\');

        foreach ($this->namespaces as $namespace) {
            if ($classNamespace === $namespace || str_starts_with($classNamespace, $namespace . '\\')) {
                return true;
            }
        }

        return false;
    }
}
