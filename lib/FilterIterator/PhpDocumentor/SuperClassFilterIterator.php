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
use function assert;
use function ltrim;
use function method_exists;
use function reset;

final class SuperClassFilterIterator extends FilterIterator
{
    private Closure $getMetadata;

    /**
     * @param Iterator<Element> $iterator
     * @phpstan-param class-string $superClass
     */
    public function __construct(Iterator $iterator, private readonly string $superClass)
    {
        parent::__construct($iterator);

        $this->getMetadata = method_exists(Class_::class, 'getMetadata') ?
            static fn (object $reflector) => $reflector->getMetadata() : /** @phpstan-ignore-line */
            static fn (object $reflector) => MetadataRegistry::getInstance()->getMetadata($reflector);
    }

    public function accept(): bool
    {
        $reflector = $this->getInnerIterator()->current();
        if ($reflector instanceof Class_ || $reflector instanceof Interface_) {
            $metadataSet = array_filter(($this->getMetadata)($reflector), static fn (object $o) => $o instanceof Metadata);
            $metadata = reset($metadataSet);
            assert($metadata instanceof Metadata);

            foreach ($metadata->superclasses as $superClass) {
                assert($superClass instanceof Class_ || $superClass instanceof Interface_);
                $name = ltrim((string) $superClass->getFqsen(), '\\');
                if ($name === $this->superClass) {
                    return true;
                }
            }
        }

        return false;
    }
}
