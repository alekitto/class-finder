<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\unit\Finder;

use Kcs\ClassFinder\Finder\ClassMapFinder;
use Kcs\ClassFinder\Fixtures\Psr0;
use Kcs\ClassFinder\Fixtures\Psr4;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Traversable;

use function iterator_to_array;
use function realpath;
use function str_ends_with;

class ClassMapFinderTest extends TestCase
{
    public function testFinderShouldBeIterable(): void
    {
        $finder = new ClassMapFinder([]);
        self::assertInstanceOf(Traversable::class, $finder);
    }

    public function testFinderShouldFilterByNamespace(): void
    {
        $finder = new ClassMapFinder([
            'Kcs\ClassFinder\Fixtures\Psr0\BarBar' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/BarBar.php',
            'Kcs\ClassFinder\Fixtures\Psr0\Foobar' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/Foobar.php',
            'Kcs\ClassFinder\Fixtures\Psr0\SubNs\FooBaz' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/SubNs/FooBaz.php',
            'Kcs\ClassFinder\Fixtures\Psr4\BarBar' => realpath(__DIR__ . '/../../../data/Composer/Psr4') . '/BarBar.php',
            'Kcs\ClassFinder\Fixtures\Psr4\Foobar' => realpath(__DIR__ . '/../../../data/Composer/Psr4') . '/Foobar.php',
        ]);

        self::assertEquals([
            Psr0\BarBar::class => new ReflectionClass(Psr0\BarBar::class),
            Psr0\Foobar::class => new ReflectionClass(Psr0\Foobar::class),
            Psr0\SubNs\FooBaz::class => new ReflectionClass(Psr0\SubNs\FooBaz::class),
            Psr4\BarBar::class => new ReflectionClass(Psr4\BarBar::class),
            Psr4\Foobar::class => new ReflectionClass(Psr4\Foobar::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByDirectory(): void
    {
        $finder = new ClassMapFinder([
            'Kcs\ClassFinder\Fixtures\Psr0\BarBar' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/BarBar.php',
            'Kcs\ClassFinder\Fixtures\Psr0\Foobar' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/Foobar.php',
            'Kcs\ClassFinder\Fixtures\Psr0\SubNs\FooBaz' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/SubNs/FooBaz.php',
            'Kcs\ClassFinder\Fixtures\Psr4\BarBar' => realpath(__DIR__ . '/../../../data/Composer/Psr4') . '/BarBar.php',
            'Kcs\ClassFinder\Fixtures\Psr4\Foobar' => realpath(__DIR__ . '/../../../data/Composer/Psr4') . '/Foobar.php',
        ]);
        $finder->in([__DIR__ . '/../../../data/Composer/Psr0/Kcs/ClassFinder/Fixtures/Psr0/SubNs']);

        self::assertEquals([
            Psr0\SubNs\FooBaz::class => new ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByInterfaceImplementation(): void
    {
        $finder = new ClassMapFinder([
            'Kcs\ClassFinder\Fixtures\Psr0\BarBar' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/BarBar.php',
            'Kcs\ClassFinder\Fixtures\Psr0\Foobar' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/Foobar.php',
            'Kcs\ClassFinder\Fixtures\Psr0\SubNs\FooBaz' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/SubNs/FooBaz.php',
            'Kcs\ClassFinder\Fixtures\Psr4\BarBar' => realpath(__DIR__ . '/../../../data/Composer/Psr4') . '/BarBar.php',
            'Kcs\ClassFinder\Fixtures\Psr4\Foobar' => realpath(__DIR__ . '/../../../data/Composer/Psr4') . '/Foobar.php',
        ]);
        $finder->implementationOf(Psr4\FooInterface::class);

        self::assertEquals([
            Psr0\BarBar::class => new ReflectionClass(Psr0\BarBar::class),
            Psr4\BarBar::class => new ReflectionClass(Psr4\BarBar::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterBySuperClass(): void
    {
        $finder = new ClassMapFinder([
            'Kcs\ClassFinder\Fixtures\Psr0\BarBar' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/BarBar.php',
            'Kcs\ClassFinder\Fixtures\Psr0\Foobar' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/Foobar.php',
            'Kcs\ClassFinder\Fixtures\Psr0\SubNs\FooBaz' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/SubNs/FooBaz.php',
            'Kcs\ClassFinder\Fixtures\Psr4\BarBar' => realpath(__DIR__ . '/../../../data/Composer/Psr4') . '/BarBar.php',
            'Kcs\ClassFinder\Fixtures\Psr4\Foobar' => realpath(__DIR__ . '/../../../data/Composer/Psr4') . '/Foobar.php',
        ]);
        $finder->subclassOf(Psr4\AbstractClass::class);

        self::assertEquals([
            Psr0\Foobar::class => new ReflectionClass(Psr0\Foobar::class),
            Psr4\Foobar::class => new ReflectionClass(Psr4\Foobar::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByAnnotation(): void
    {
        $finder = new ClassMapFinder([
            'Kcs\ClassFinder\Fixtures\Psr0\BarBar' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/BarBar.php',
            'Kcs\ClassFinder\Fixtures\Psr0\Foobar' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/Foobar.php',
            'Kcs\ClassFinder\Fixtures\Psr0\SubNs\FooBaz' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/SubNs/FooBaz.php',
            'Kcs\ClassFinder\Fixtures\Psr4\BarBar' => realpath(__DIR__ . '/../../../data/Composer/Psr4') . '/BarBar.php',
            'Kcs\ClassFinder\Fixtures\Psr4\Foobar' => realpath(__DIR__ . '/../../../data/Composer/Psr4') . '/Foobar.php',
        ]);
        $finder->annotatedBy(Psr4\SubNs\FooBaz::class);

        self::assertEquals([
            Psr0\SubNs\FooBaz::class => new ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByAttribute(): void
    {
        $finder = new ClassMapFinder([
            'Kcs\ClassFinder\Fixtures\Psr0\BarBar' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/BarBar.php',
            'Kcs\ClassFinder\Fixtures\Psr0\Foobar' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/Foobar.php',
            'Kcs\ClassFinder\Fixtures\Psr0\SubNs\FooBaz' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/SubNs/FooBaz.php',
            'Kcs\ClassFinder\Fixtures\Psr4\BarBar' => realpath(__DIR__ . '/../../../data/Composer/Psr4') . '/BarBar.php',
            'Kcs\ClassFinder\Fixtures\Psr4\Foobar' => realpath(__DIR__ . '/../../../data/Composer/Psr4') . '/Foobar.php',
        ]);
        $finder->withAttribute(Psr4\SubNs\FooBaz::class);

        self::assertEquals([
            Psr0\SubNs\FooBaz::class => new ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByPathCallback(): void
    {
        $finder = new ClassMapFinder([
            'Kcs\ClassFinder\Fixtures\Psr0\BarBar' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/BarBar.php',
            'Kcs\ClassFinder\Fixtures\Psr0\Foobar' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/Foobar.php',
            'Kcs\ClassFinder\Fixtures\Psr0\SubNs\FooBaz' => realpath(__DIR__ . '/../../../data/Composer/Psr0') . '/Kcs/ClassFinder/Fixtures/Psr0/SubNs/FooBaz.php',
            'Kcs\ClassFinder\Fixtures\Psr4\BarBar' => realpath(__DIR__ . '/../../../data/Composer/Psr4') . '/BarBar.php',
            'Kcs\ClassFinder\Fixtures\Psr4\Foobar' => realpath(__DIR__ . '/../../../data/Composer/Psr4') . '/Foobar.php',
        ]);
        $finder->pathFilter(static fn (string $path): bool => ! str_ends_with($path, 'BarBar.php'));

        self::assertEquals([
            Psr0\Foobar::class => new ReflectionClass(Psr0\Foobar::class),
            Psr0\SubNs\FooBaz::class => new ReflectionClass(Psr0\SubNs\FooBaz::class),
            Psr4\Foobar::class => new ReflectionClass(Psr4\Foobar::class),
        ], iterator_to_array($finder));
    }
}
