<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\PhpDocumentor;

use Closure;
use FilterIterator;
use Iterator;
use Kcs\ClassFinder\Util\Offline\Metadata;
use Kcs\ClassFinder\Util\PhpDocumentor\MetadataRegistry;
use phpDocumentor\Reflection\Element;
use phpDocumentor\Reflection\Php\Class_;
use phpDocumentor\Reflection\Php\Interface_;

use function array_filter;
use function array_map;
use function assert;
use function count;
use function in_array;
use function ltrim;
use function method_exists;
use function reset;

final class InterfaceImplementationFilterIterator extends FilterIterator
{
    private Closure $getMetadata;

    /**
     * @param Iterator<Element> $iterator
     * @param string[] $interfaces
     * @phpstan-param class-string[] $interfaces
     */
    public function __construct(Iterator $iterator, private readonly array $interfaces)
    {
        parent::__construct($iterator);

        $this->getMetadata = method_exists(Class_::class, 'getMetadata') ?
            static fn (object $reflector) => $reflector->getMetadata() : /** @phpstan-ignore-line */
            static fn (object $reflector) => MetadataRegistry::getInstance()->getMetadata($reflector);
    }

    public function accept(): bool
    {
        $reflector = $this->getInnerIterator()->current();
        if (! $reflector instanceof Class_) {
            return false;
        }

        $metadataSet = array_filter(($this->getMetadata)($reflector), static fn (object $o) => $o instanceof Metadata);
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
