<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Reflection;

interface ReflectorFactoryInterface
{
    /**
     * Builds a reflector object.
     *
     * Default implementation (NativeReflectorFactory) will always return an instance
     * of ReflectionClass, but this is not guaranteed for other factories.
     *
     * @phpstan-param class-string $className
     */
    public function reflect(string $className): object;
}
