<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\PhpDocumentor;

use FilterIterator;
use Iterator;
use phpDocumentor\Reflection\Element;
use phpDocumentor\Reflection\Php\Class_;
use phpDocumentor\Reflection\Php\Interface_;
use phpDocumentor\Reflection\Php\Trait_;
use RuntimeException;

use function assert;

final class AttributeFilterIterator extends FilterIterator
{
    // phpcs:ignore SlevomatCodingStandard.Classes.UnusedPrivateElements.WriteOnlyProperty
    private string $attribute;

    /**
     * @param Iterator<Element> $iterator
     *
     * @phpstan-param class-string $attribute
     */
    public function __construct(Iterator $iterator, string $attribute)
    {
        parent::__construct($iterator);
        $this->attribute = $attribute;
    }

    public function accept(): bool
    {
        $reflector = $this->getInnerIterator()->current();
        assert($reflector instanceof Class_ || $reflector instanceof Interface_ || $reflector instanceof Trait_);

        throw new RuntimeException('Attributes support is not implemented in phpdocumentor');
    }
}
