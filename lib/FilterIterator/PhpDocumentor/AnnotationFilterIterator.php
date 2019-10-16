<?php declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\PhpDocumentor;

use Doctrine\Common\Annotations\AnnotationReader;
use phpDocumentor\Reflection\BaseReflector;

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
        $this->annotation = $annotation;
    }

    /**
     * {@inheritdoc}
     */
    public function accept(): bool
    {
        /** @var BaseReflector $reflector */
        $reflector = $this->getInnerIterator()->current();
        $docblock = $reflector->getDocBlock();

        if (null !== $docblock) {
            if ($docblock->hasTag($this->annotation)) {
                return true;
            }

            foreach ($docblock->getContext()->getNamespaceAliases() as $alias => $name) {
                if ($name === $this->annotation && $docblock->hasTag($alias)) {
                    return true;
                }
            }
        }

        return true;
    }
}
