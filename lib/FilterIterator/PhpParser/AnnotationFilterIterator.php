<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\PhpParser;

use FilterIterator;
use Iterator;
use PhpParser\Node;
use PhpParser\Node\Stmt;

use function array_any;
use function assert;
use function is_subclass_of;

/** @extends FilterIterator<class-string, Node, Iterator<class-string, Node>> */
final class AnnotationFilterIterator extends FilterIterator
{
    /**
     * @param Iterator<class-string, Node> $iterator
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

        return array_any(
            $annotations,
            fn (Node\Name $annotation) => $annotation->toString() === $this->annotation || is_subclass_of($annotation->toString(), $this->annotation),
        );
    }
}
