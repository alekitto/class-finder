<?php declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\Reflection;

use Doctrine\Common\Annotations\AnnotationReader;

final class AnnotationFilterIterator extends \FilterIterator
{
    /**
     * @var string
     */
    private $annotation;

    /**
     * @var AnnotationReader
     */
    private $reader;

    public function __construct(\Iterator $iterator, string $annotation)
    {
        parent::__construct($iterator);

        $this->reader = new AnnotationReader();
        $this->annotation = $annotation;
    }

    /**
     * {@inheritdoc}
     */
    public function accept()
    {
        return null !== $this->reader->getClassAnnotation($this->getInnerIterator()->current(), $this->annotation);
    }
}
