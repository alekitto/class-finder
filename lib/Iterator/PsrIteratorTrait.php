<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

trait PsrIteratorTrait
{
    use RecursiveIteratorTrait;

    protected function exists(string $className): bool
    {
        return class_exists($className, false) ||
            interface_exists($className, false) ||
            trait_exists($className, false);
    }
}
