<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\Reflection;

use FilterIterator;
use Iterator;
use Kcs\ClassFinder\PathNormalizer;
use Reflector;

use function array_map;
use function strpos;

final class DirectoryFilterIterator extends FilterIterator
{
    /** @var string[] */
    private array $dirs;

    /**
     * @param Iterator<Reflector> $iterator
     * @param string[] $dirs
     */
    public function __construct(Iterator $iterator, array $dirs)
    {
        parent::__construct($iterator);

        $this->dirs = (static function (string ...$dirs) {
            return array_map(PathNormalizer::class . '::resolvePath', $dirs);
        })(...$dirs);
    }

    public function accept(): bool
    {
        $reflectionClass = $this->getInnerIterator()->current();
        if ($reflectionClass->isInternal()) {
            return false;
        }

        foreach ($this->dirs as $dir) {
            if (strpos($reflectionClass->getFileName(), $dir) === 0) {
                return true;
            }
        }

        return false;
    }
}
