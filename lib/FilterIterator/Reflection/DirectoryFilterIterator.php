<?php declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\Reflection;

use Kcs\ClassFinder\PathNormalizer;

final class DirectoryFilterIterator extends \FilterIterator
{
    /**
     * @var string[]
     */
    private $dirs;

    public function __construct(\Iterator $iterator, array $dirs)
    {
        parent::__construct($iterator);

        $this->dirs = (function (string ...$dirs) {
            return array_map(PathNormalizer::class.'::resolvePath', $dirs);
        })(...$dirs);
    }

    /**
     * {@inheritdoc}
     */
    public function accept()
    {
        $reflectionClass = new \ReflectionClass($this->getInnerIterator()->key());
        foreach ($this->dirs as $dir) {
            if (0 === strpos($reflectionClass->getFileName(), $dir)) {
                return true;
            }
        }

        return false;
    }
}
