<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\unit\Iterator;

use Kcs\ClassFinder\Fixtures\Psr4;
use Kcs\ClassFinder\Iterator\Psr4Iterator;
use Kcs\ClassFinder\Reflection\NativeReflectorFactory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use function iterator_to_array;
use function realpath;

class Psr4IteratorTest extends TestCase
{
    public function testIteratorShouldWork(): void
    {
        $iterator = new Psr4Iterator(
            'Kcs\\ClassFinder\\Fixtures\\Psr4\\',
            realpath(__DIR__ . '/../../../data/Composer/Psr4'),
            new NativeReflectorFactory(),
        );

        self::assertEquals([
            Psr4\BarBar::class => new ReflectionClass(Psr4\BarBar::class),
            Psr4\Foobar::class => new ReflectionClass(Psr4\Foobar::class),
            Psr4\AbstractClass::class => new ReflectionClass(Psr4\AbstractClass::class),
            Psr4\FooInterface::class => new ReflectionClass(Psr4\FooInterface::class),
            Psr4\FooTrait::class => new ReflectionClass(Psr4\FooTrait::class),
            Psr4\SubNs\FooBaz::class => new ReflectionClass(Psr4\SubNs\FooBaz::class),
        ], iterator_to_array($iterator));
    }

    public function testIteratorShouldCallPathCallback(): void
    {
        $iterator = new Psr4Iterator(
            'Kcs\\ClassFinder\\Fixtures\\Psr4\\',
            realpath(__DIR__ . '/../../../data/Composer/Psr4'),
            new NativeReflectorFactory(),
            pathCallback: function (string $path): bool {
                return !str_ends_with($path, 'BarBar.php');
            },
        );

        self::assertEquals([
            Psr4\Foobar::class => new ReflectionClass(Psr4\Foobar::class),
            Psr4\AbstractClass::class => new ReflectionClass(Psr4\AbstractClass::class),
            Psr4\FooInterface::class => new ReflectionClass(Psr4\FooInterface::class),
            Psr4\FooTrait::class => new ReflectionClass(Psr4\FooTrait::class),
            Psr4\SubNs\FooBaz::class => new ReflectionClass(Psr4\SubNs\FooBaz::class),
        ], iterator_to_array($iterator));
    }
}
