<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\PhpParser;

use FilterIterator;
use Iterator;
use Kcs\ClassFinder\Util\Offline\Metadata;
use PhpParser\Node\Stmt;

use function assert;
use function ltrim;

final class SuperClassFilterIterator extends FilterIterator
{
    /**
     * @param Iterator<Stmt\ClassLike> $iterator
     * @phpstan-param class-string $superClass
     */
    public function __construct(Iterator $iterator, private readonly string $superClass)
    {
        parent::__construct($iterator);
    }

    public function accept(): bool
    {
        $reflector = $this->getInnerIterator()->current();
        if ($reflector instanceof Stmt\Class_ || $reflector instanceof Stmt\Interface_) {
            $metadata = $reflector->getAttribute(Metadata::METADATA_KEY);
            assert($metadata instanceof Metadata);

            foreach ($metadata->superclasses as $superClass) {
                assert($superClass instanceof Stmt\Class_ || $superClass instanceof Stmt\Interface_);
                $name = ltrim((string) $superClass->namespacedName, '\\');
                if ($name === $this->superClass) {
                    return true;
                }
            }
        }

        return false;
    }
}
