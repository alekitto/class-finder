<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\PhpParser;

use FilterIterator;
use Iterator;
use PhpParser\Node;
use PhpParser\Node\Stmt;

use function array_any;
use function array_push;
use function assert;

/** @extends FilterIterator<class-string, Node, Iterator<class-string, Node>> */
final class AttributeFilterIterator extends FilterIterator
{
    /**
     * @param Iterator<class-string, Node> $iterator
     * @phpstan-param class-string $attribute
     */
    public function __construct(Iterator $iterator, private readonly string $attribute)
    {
        parent::__construct($iterator);
    }

    public function accept(): bool
    {
        $reflector = $this->getInnerIterator()->current();
        assert($reflector instanceof Stmt\ClassLike);

        $attrs = [];
        foreach ($reflector->attrGroups as $attrGroup) {
            array_push($attrs, ...$attrGroup->attrs);
        }

        return array_any(
            $attrs,
            fn (Node\Attribute $attr): bool => $attr->name->toString() === $this->attribute,
        );
    }
}
