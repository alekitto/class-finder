<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\Reflection;

use FilterIterator;
use Iterator;
use Kcs\ClassFinder\PathNormalizer;
use ReflectionClass;

use function array_map;
use function is_string;
use function str_starts_with;

/**
 * @template-covariant TValue of ReflectionClass
 * @template T of Iterator<class-string, TValue>
 * @template-extends FilterIterator<class-string, TValue, T>
 */
final class DirectoryFilterIterator extends FilterIterator
{
    /** @var string[] */
    private array $dirs;

    /**
     * @param T $iterator
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
            $filename = $reflectionClass->getFileName();
            if (is_string($filename) && str_starts_with($filename, $dir)) {
                return true;
            }
        }

        return false;
    }
}
