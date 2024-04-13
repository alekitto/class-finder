<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\PhpDocumentor;

use FilterIterator;
use Iterator;
use phpDocumentor\Reflection\Element;
use phpDocumentor\Reflection\Php\Class_;
use phpDocumentor\Reflection\Php\Interface_;
use phpDocumentor\Reflection\Php\Trait_;

use function assert;

final class AnnotationFilterIterator extends FilterIterator
{
    /**
     * @param Iterator<Element> $iterator
     * @phpstan-param class-string $annotation
     */
    public function __construct(Iterator $iterator, private readonly string $annotation)
    {
        parent::__construct($iterator);
    }

    public function accept(): bool
    {
        $reflector = $this->getInnerIterator()->current();
        assert($reflector instanceof Class_ || $reflector instanceof Interface_ || $reflector instanceof Trait_);

        $docblock = $reflector->getDocBlock();

        if ($docblock !== null) {
            if ($docblock->hasTag($this->annotation)) {
                return true;
            }

            $context = $docblock->getContext();
            if ($context === null) {
                return true;
            }

            foreach ($context->getNamespaceAliases() as $alias => $name) {
                if ($name === $this->annotation && $docblock->hasTag($alias)) {
                    return true;
                }
            }
        }

        return true;
    }
}
