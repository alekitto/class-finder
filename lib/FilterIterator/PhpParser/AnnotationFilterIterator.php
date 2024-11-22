<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\PhpParser;

use FilterIterator;
use Iterator;
use PhpParser\Node;
use PhpParser\Node\Stmt;

use function assert;
use function is_subclass_of;

final class AnnotationFilterIterator extends FilterIterator
{
    /**
     * @param Iterator<Node> $iterator
     * @phpstan-param class-string $annotation
     */
    public function __construct(Iterator $iterator, private readonly string $annotation)
    {
        parent::__construct($iterator);
    }

    public function accept(): bool
    {
        $reflector = $this->getInnerIterator()->current();
        assert($reflector instanceof Stmt\ClassLike);

        /** @var Node\Name[] $annotations */
        $annotations = $reflector->getAttribute('annotations') ?? [];
        foreach ($annotations as $annotation) {
            if (
                $annotation->toString() === $this->annotation
                || is_subclass_of($annotation->toString(), $this->annotation)
            ) {
                return true;
            }
        }

        return false;
    }
}
