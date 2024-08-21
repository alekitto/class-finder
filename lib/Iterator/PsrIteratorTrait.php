<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use function class_exists;
use function interface_exists;
use function Safe\preg_match;
use function str_starts_with;
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

    protected function validNamespace(string $class): bool
    {
        if (! str_starts_with($class, $this->namespace)) {
            return false;
        }

        if (! parent::validNamespace($class)) {
            return false;
        }

        return (bool) preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*+(?:\\\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*+)*+$/', $class);
    }
}
