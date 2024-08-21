<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\Reflection;

use Doctrine\Common\Annotations\AnnotationReader;
use FilterIterator;
use Iterator;
use ReflectionClass;

/**
 * @template-covariant TValue of ReflectionClass
 * @template T of Iterator<class-string, TValue>
 * @template-extends FilterIterator<class-string, TValue, T>
 */
final class AnnotationFilterIterator extends FilterIterator
{
    private AnnotationReader $reader;

    /**
     * @param T $iterator
     * @phpstan-param class-string $annotation
     */
    public function __construct(Iterator $iterator, private readonly string $annotation)
    {
        parent::__construct($iterator);

        $this->reader = new AnnotationReader();
    }

    public function accept(): bool
    {
        return $this->reader->getClassAnnotation($this->getInnerIterator()->current(), $this->annotation) !== null;
    }
}
