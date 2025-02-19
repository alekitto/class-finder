<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\unit\Reflection;

use Kcs\ClassFinder\Reflection\BetterReflectionReflectorFactory;
use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\Reflection\ReflectionClass;

class BetterReflectionReflectorFactoryTest extends TestCase
{
    public function testShouldReturnABetterReflectionReflectorObject(): void
    {
        if (!class_exists(ReflectionClass::class)) {
            self::markTestSkipped();
        }

        $factory = new BetterReflectionReflectorFactory();
        self::assertInstanceOf(ReflectionClass::class, $factory->reflect(self::class));
    }
}
