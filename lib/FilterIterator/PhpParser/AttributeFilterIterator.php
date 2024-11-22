<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\PhpParser;

use FilterIterator;
use Iterator;
use PhpParser\Node;
use PhpParser\Node\Stmt;

use function array_filter;
use function array_push;
use function assert;
use function count;

final class AttributeFilterIterator extends FilterIterator
{
    /**
     * @param Iterator<Node> $iterator
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

        return count(array_filter(
            $attrs,
            fn (Node\Attribute $attr): bool => $attr->name->toString() === $this->attribute,
        )) > 0;
    }
}
