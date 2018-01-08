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
     * @inheritDoc
     */
    public function accept()
    {
        $reflectionClass = new \ReflectionClass($this->getInnerIterator()->key());
        foreach ($this->dirs as $dir) {
            if (strpos($reflectionClass->getFileName(), $dir) === 0) {
                return true;
            }
        }

        return false;
    }
}
