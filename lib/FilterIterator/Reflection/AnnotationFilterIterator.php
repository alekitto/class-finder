<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\Reflection;

use Doctrine\Common\Annotations\AnnotationReader;
use FilterIterator;
use Iterator;
use Reflector;

final class AnnotationFilterIterator extends FilterIterator
{
    /** @phpstan-var class-string */
    private string $annotation;
    private AnnotationReader $reader;

    /**
     * @param Iterator<Reflector> $iterator
     * @phpstan-param class-string $annotation
     */
    public function __construct(Iterator $iterator, string $annotation)
    {
        parent::__construct($iterator);

        $this->reader = new AnnotationReader();
        $this->annotation = $annotation;
    }

    public function accept(): bool
    {
        return $this->reader->getClassAnnotation($this->getInnerIterator()->current(), $this->annotation) !== null;
    }
}
