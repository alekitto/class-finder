<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\unit\Iterator;

use Kcs\ClassFinder\Fixtures\Psr0;
use Kcs\ClassFinder\Fixtures\Psr4;
use Kcs\ClassFinder\Iterator\ClassMapIterator;
use Kcs\ClassFinder\Reflection\NativeReflectorFactory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function iterator_to_array;
use function realpath;
use function str_ends_with;

class ClassMapIteratorTest extends TestCase
{
    public function testIteratorShouldWork(): void
    {
        $iterator = new ClassMapIterator(
            [
                'Kcs\ClassFinder\Fixtures\Psr0\BarBar' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/BarBar.php',
                'Kcs\ClassFinder\Fixtures\Psr0\Foobar' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/Foobar.php',
                'Kcs\ClassFinder\Fixtures\Psr4\Foobar' => realpath(__DIR__ . '/../../../data/Composer/Psr4') . '/Foobar.php',
            ],
            new NativeReflectorFactory(),
        );

        self::assertEquals([
            Psr0\BarBar::class => new ReflectionClass(Psr0\BarBar::class),
            Psr0\Foobar::class => new ReflectionClass(Psr0\Foobar::class),
            Psr4\Foobar::class => new ReflectionClass(Psr4\Foobar::class),
        ], iterator_to_array($iterator));
    }

    public function testIteratorShouldCallPathCallback(): void
    {
        $iterator = new ClassMapIterator(
            [
                'Kcs\ClassFinder\Fixtures\Psr0\BarBar' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/BarBar.php',
                'Kcs\ClassFinder\Fixtures\Psr0\Foobar' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/Foobar.php',
                'Kcs\ClassFinder\Fixtures\Psr4\Foobar' => realpath(__DIR__ . '/../../../data/Composer/Psr4') . '/Foobar.php',
            ],
            new NativeReflectorFactory(),
            pathCallback: static function (string $path): bool {
                return ! str_ends_with($path, 'BarBar.php');
            },
        );

        self::assertEquals([
            Psr0\Foobar::class => new ReflectionClass(Psr0\Foobar::class),
            Psr4\Foobar::class => new ReflectionClass(Psr4\Foobar::class),
        ], iterator_to_array($iterator));
    }
}
