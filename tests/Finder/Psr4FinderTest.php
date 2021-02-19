<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\Finder;

use Kcs\ClassFinder\Finder\Psr4Finder;
use Kcs\ClassFinder\Fixtures\Psr4;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Traversable;

use function iterator_to_array;

class Psr4FinderTest extends TestCase
{
    public function testFinderShouldBeIterable(): void
    {
        $finder = new Psr4Finder(__NAMESPACE__, __DIR__);
        self::assertInstanceOf(Traversable::class, $finder);
    }

    public function testFinderShouldFilterByNamespace(): void
    {
        $finder = new Psr4Finder('Kcs\ClassFinder\Fixtures\Psr4', __DIR__ . '/../../data/Composer/Psr4');

        self::assertEquals([
            Psr4\BarBar::class => new ReflectionClass(Psr4\BarBar::class),
            Psr4\Foobar::class => new ReflectionClass(Psr4\Foobar::class),
            Psr4\AbstractClass::class => new ReflectionClass(Psr4\AbstractClass::class),
            Psr4\FooInterface::class => new ReflectionClass(Psr4\FooInterface::class),
            Psr4\FooTrait::class => new ReflectionClass(Psr4\FooTrait::class),
            Psr4\SubNs\FooBaz::class => new ReflectionClass(Psr4\SubNs\FooBaz::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByDirectory(): void
    {
        $finder = new Psr4Finder('Kcs\ClassFinder\Fixtures\Psr4', __DIR__ . '/../../data/Composer/Psr4');
        $finder->in([__DIR__ . '/../../data/Composer/Psr4/SubNs']);

        self::assertEquals([
            Psr4\SubNs\FooBaz::class => new ReflectionClass(Psr4\SubNs\FooBaz::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByInterfaceImplementation(): void
    {
        $finder = new Psr4Finder('Kcs\ClassFinder\Fixtures\Psr4', __DIR__ . '/../../data/Composer/Psr4');
        $finder->implementationOf(Psr4\FooInterface::class);

        self::assertEquals([
            Psr4\BarBar::class => new ReflectionClass(Psr4\BarBar::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterBySuperClass(): void
    {
        $finder = new Psr4Finder('Kcs\ClassFinder\Fixtures\Psr4', __DIR__ . '/../../data/Composer/Psr4');
        $finder->subclassOf(Psr4\AbstractClass::class);

        self::assertEquals([
            Psr4\Foobar::class => new ReflectionClass(Psr4\Foobar::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByAnnotation(): void
    {
        $finder = new Psr4Finder('Kcs\ClassFinder\Fixtures\Psr4', __DIR__ . '/../../data/Composer/Psr4');
        $finder->annotatedBy(Psr4\SubNs\FooBaz::class);

        self::assertEquals([
            Psr4\AbstractClass::class => new ReflectionClass(Psr4\AbstractClass::class),
        ], iterator_to_array($finder));
    }

    /**
     * @requires PHP >= 8.0
     */
    public function testFinderShouldFilterByAttribute(): void
    {
        $finder = new Psr4Finder('Kcs\ClassFinder\Fixtures\Psr4', __DIR__ . '/../../data/Composer/Psr4');
        $finder->withAttribute(Psr4\SubNs\FooBaz::class);

        self::assertEquals([
            Psr4\AbstractClass::class => new ReflectionClass(Psr4\AbstractClass::class),
        ], iterator_to_array($finder));
    }
}
