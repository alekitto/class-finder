<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\PhpDocumentor;

use FilterIterator;
use Iterator;
use Kcs\ClassFinder\Util\Offline\Metadata;
use phpDocumentor\Reflection\Element;
use phpDocumentor\Reflection\Php\Class_;
use phpDocumentor\Reflection\Php\Interface_;

use function array_filter;
use function array_map;
use function assert;
use function count;
use function in_array;
use function ltrim;
use function reset;

final class InterfaceImplementationFilterIterator extends FilterIterator
{
    /**
     * @param Iterator<Element> $iterator
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
        if (! $reflector instanceof Class_) {
            return false;
        }

        $metadataSet = array_filter($reflector->getMetadata(), static fn (object $o) => $o instanceof Metadata);
        $metadata = reset($metadataSet);
        assert($metadata instanceof Metadata);

        $implementations = array_map(
            static fn (Interface_ $i) => ltrim((string) $i->getFqsen(), '\\'),
            array_filter($metadata->superclasses, static fn (object $o) => $o instanceof Interface_),
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
