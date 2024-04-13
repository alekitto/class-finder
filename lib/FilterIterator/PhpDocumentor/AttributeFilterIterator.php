<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\PhpDocumentor;

use FilterIterator;
use Iterator;
use phpDocumentor\Reflection\Element;
use phpDocumentor\Reflection\Php\Attribute;
use phpDocumentor\Reflection\Php\Class_;
use phpDocumentor\Reflection\Php\Interface_;
use phpDocumentor\Reflection\Php\Trait_;
use RuntimeException;

use function array_filter;
use function assert;
use function count;
use function ltrim;
use function method_exists;

final class AttributeFilterIterator extends FilterIterator
{
    /**
     * @param Iterator<Element> $iterator
     * @phpstan-param class-string $attribute
     */
    public function __construct(Iterator $iterator, private readonly string $attribute)
    {
        parent::__construct($iterator);
    }

    public function accept(): bool
    {
        $reflector = $this->getInnerIterator()->current();
        assert($reflector instanceof Class_ || $reflector instanceof Interface_ || $reflector instanceof Trait_);

        if (! method_exists($reflector, 'getAttributes')) {
            throw new RuntimeException('Attributes support is not implemented in phpdocumentor');
        }

        return count(array_filter(
            $reflector->getAttributes(),
            fn (Attribute $attr): bool => ltrim((string) $attr->getFqsen(), '\\') === $this->attribute,
        )) > 0;
    }
}
