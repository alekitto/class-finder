<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Reflection;

use Roave\BetterReflection\BetterReflection;

class BetterReflectionReflectorFactory implements ReflectorFactoryInterface
{
    public function reflect(string $className): object
    {
        return (new BetterReflection())
            ->reflector()
            ->reflectClass($className);
    }
}
