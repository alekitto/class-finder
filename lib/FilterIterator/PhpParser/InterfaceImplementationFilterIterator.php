<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\PhpParser;

use FilterIterator;
use Iterator;
use Kcs\ClassFinder\Util\Offline\Metadata;
use PhpParser\Node\Stmt;

use function array_filter;
use function array_map;
use function assert;
use function count;
use function in_array;
use function ltrim;

final class InterfaceImplementationFilterIterator extends FilterIterator
{
    /**
     * @param Iterator<Stmt\ClassLike> $iterator
     * @param string[] $interfaces
     * @phpstan-param class-string[] $interfaces
     */
    public function __construct(Iterator $iterator, private readonly array $interfaces)
    {
        parent::__construct($iterator);
    }

    public function accept(): bool
    {
        $reflector = $this->getInnerIterator()->current();
        if (! $reflector instanceof Stmt\Class_) {
            return false;
        }

        $metadata = $reflector->getAttribute(Metadata::METADATA_KEY);
        assert($metadata instanceof Metadata);

        $implementations = array_map(
            static fn (Stmt\Interface_ $i) => ltrim((string) $i->namespacedName, '\\'),
            array_filter($metadata->superclasses, static fn (object $o) => $o instanceof Stmt\Interface_),
        );

        return count(
            array_filter(
                array_map(
                    static fn (string $interface) => in_array($interface, $implementations, true),
                    $this->interfaces,
                ),
                static fn (bool $r) => $r === false,
            ),
        ) === 0;
    }
}
