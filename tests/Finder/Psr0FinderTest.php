<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\Finder;

use Kcs\ClassFinder\Finder\Psr0Finder;
use Kcs\ClassFinder\Fixtures\Psr0;
use Kcs\ClassFinder\Fixtures\Psr4;
use PHPUnit\Framework\TestCase;

class Psr0FinderTest extends TestCase
{
    public function testFinderShouldBeIterable()
    {
        $finder = new Psr0Finder(__NAMESPACE__, __DIR__);
        $this->assertInstanceOf(\Traversable::class, $finder);
    }

    public function testFinderShouldFilterByNamespace()
    {
        $finder = new Psr0Finder('Kcs\ClassFinder\Fixtures\Psr0', __DIR__.'/../../data/Composer/Psr0');

        $this->assertEquals([
            Psr0\BarBar::class => new \ReflectionClass(Psr0\BarBar::class),
            Psr0\Foobar::class => new \ReflectionClass(Psr0\Foobar::class),
            Psr0\SubNs\FooBaz::class => new \ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByDirectory()
    {
        $finder = new Psr0Finder('Kcs\ClassFinder\Fixtures\Psr0', __DIR__.'/../../data/Composer/Psr0');
        $finder->in([__DIR__.'/../../data/Composer/Psr0/Kcs/ClassFinder/Fixtures/Psr0/SubNs']);

        $this->assertEquals([
            Psr0\SubNs\FooBaz::class => new \ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByInterfaceImplementation()
    {
        $finder = new Psr0Finder('Kcs\ClassFinder\Fixtures\Psr0', __DIR__.'/../../data/Composer/Psr0');
        $finder->implementationOf(Psr4\FooInterface::class);

        $this->assertEquals([
            Psr0\BarBar::class => new \ReflectionClass(Psr0\BarBar::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterBySuperClass()
    {
        $finder = new Psr0Finder('Kcs\ClassFinder\Fixtures\Psr0', __DIR__.'/../../data/Composer/Psr0');
        $finder->subclassOf(Psr4\AbstractClass::class);

        $this->assertEquals([
            Psr0\Foobar::class => new \ReflectionClass(Psr0\Foobar::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByAnnotation()
    {
        $finder = new Psr0Finder('Kcs\ClassFinder\Fixtures\Psr0', __DIR__.'/../../data/Composer/Psr0');
        $finder->annotatedBy(Psr4\SubNs\FooBaz::class);

        $this->assertEquals([
            Psr0\SubNs\FooBaz::class => new \ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], iterator_to_array($finder));
    }
}
