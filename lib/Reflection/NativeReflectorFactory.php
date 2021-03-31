<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Reflection;

use ReflectionClass;

class NativeReflectorFactory implements ReflectorFactoryInterface
{
    public function reflect(string $className): object
    {
        return new ReflectionClass($className);
    }
}
