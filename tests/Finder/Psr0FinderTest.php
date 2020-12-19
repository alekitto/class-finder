<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\Finder;

use Kcs\ClassFinder\Finder\Psr0Finder;
use Kcs\ClassFinder\Fixtures\Psr0;
use Kcs\ClassFinder\Fixtures\Psr4;
use PHPUnit\Framework\TestCase;

class Psr0FinderTest extends TestCase
{
    public function testFinderShouldBeIterable(): void
    {
        $finder = new Psr0Finder(__NAMESPACE__, __DIR__);
        self::assertInstanceOf(\Traversable::class, $finder);
    }

    public function testFinderShouldFilterByNamespace(): void
    {
        $finder = new Psr0Finder('Kcs\ClassFinder\Fixtures\Psr0', __DIR__.'/../../data/Composer/Psr0');

        self::assertEquals([
            Psr0\BarBar::class => new \ReflectionClass(Psr0\BarBar::class),
            Psr0\Foobar::class => new \ReflectionClass(Psr0\Foobar::class),
            Psr0\SubNs\FooBaz::class => new \ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], \iterator_to_array($finder));
    }

    public function testFinderShouldFilterByDirectory(): void
    {
        $finder = new Psr0Finder('Kcs\ClassFinder\Fixtures\Psr0', __DIR__.'/../../data/Composer/Psr0');
        $finder->in([__DIR__.'/../../data/Composer/Psr0/Kcs/ClassFinder/Fixtures/Psr0/SubNs']);

        self::assertEquals([
            Psr0\SubNs\FooBaz::class => new \ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], \iterator_to_array($finder));
    }

    public function testFinderShouldFilterByInterfaceImplementation(): void
    {
        $finder = new Psr0Finder('Kcs\ClassFinder\Fixtures\Psr0', __DIR__.'/../../data/Composer/Psr0');
        $finder->implementationOf(Psr4\FooInterface::class);

        self::assertEquals([
            Psr0\BarBar::class => new \ReflectionClass(Psr0\BarBar::class),
        ], \iterator_to_array($finder));
    }

    public function testFinderShouldFilterBySuperClass(): void
    {
        $finder = new Psr0Finder('Kcs\ClassFinder\Fixtures\Psr0', __DIR__.'/../../data/Composer/Psr0');
        $finder->subclassOf(Psr4\AbstractClass::class);

        self::assertEquals([
            Psr0\Foobar::class => new \ReflectionClass(Psr0\Foobar::class),
        ], \iterator_to_array($finder));
    }

    public function testFinderShouldFilterByAnnotation(): void
    {
        $finder = new Psr0Finder('Kcs\ClassFinder\Fixtures\Psr0', __DIR__.'/../../data/Composer/Psr0');
        $finder->annotatedBy(Psr4\SubNs\FooBaz::class);

        self::assertEquals([
            Psr0\SubNs\FooBaz::class => new \ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], \iterator_to_array($finder));
    }

    /**
     * @requires PHP >= 8.0
     */
    public function testFinderShouldFilterByAttribute(): void
    {
        $finder = new Psr0Finder('Kcs\ClassFinder\Fixtures\Psr0', __DIR__.'/../../data/Composer/Psr0');
        $finder->withAttribute(Psr4\SubNs\FooBaz::class);

        self::assertEquals([
            Psr0\SubNs\FooBaz::class => new \ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], \iterator_to_array($finder));
    }
}
