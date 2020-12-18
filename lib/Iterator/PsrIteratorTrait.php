<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use function class_exists;
use function interface_exists;
use function trait_exists;

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
