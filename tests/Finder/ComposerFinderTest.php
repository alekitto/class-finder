<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\Finder;

use Kcs\ClassFinder\Finder\ComposerFinder;
use Kcs\ClassFinder\Fixtures\Psr0;
use Kcs\ClassFinder\Fixtures\Psr4;
use Kcs\ClassFinder\Fixtures\Psr4WithClassMap;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Debug\DebugClassLoader;

class ComposerFinderTest extends TestCase
{
    public function testFinderShouldBeIterable()
    {
        $finder = new ComposerFinder();
        self::assertInstanceOf(\Traversable::class, $finder);
    }

    public function testShouldNotThrowWhenSymfonyDebugClassLoaderIsEnabled()
    {
        DebugClassLoader::enable();

        try {
            $finder = new ComposerFinder();
            self::assertInstanceOf(ComposerFinder::class, $finder);
        } finally {
            DebugClassLoader::disable();
        }
    }

    public function testFinderShouldFilterByNamespace()
    {
        $finder = new ComposerFinder();
        $finder->inNamespace(['Kcs\ClassFinder\Fixtures\Psr4']);

        self::assertEquals([
            Psr4\BarBar::class => new \ReflectionClass(Psr4\BarBar::class),
            Psr4\Foobar::class => new \ReflectionClass(Psr4\Foobar::class),
            Psr4\AbstractClass::class => new \ReflectionClass(Psr4\AbstractClass::class),
            Psr4\FooInterface::class => new \ReflectionClass(Psr4\FooInterface::class),
            Psr4\FooTrait::class => new \ReflectionClass(Psr4\FooTrait::class),
            Psr4\SubNs\FooBaz::class => new \ReflectionClass(Psr4\SubNs\FooBaz::class),
        ], \iterator_to_array($finder));
    }

    public function testFinderShouldFilterByDirectory()
    {
        $finder = new ComposerFinder();
        $finder->in([__DIR__.'/../../data/Composer/Psr0']);

        self::assertEquals([
            Psr0\BarBar::class => new \ReflectionClass(Psr0\BarBar::class),
            Psr0\Foobar::class => new \ReflectionClass(Psr0\Foobar::class),
            Psr0\SubNs\FooBaz::class => new \ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], \iterator_to_array($finder));
    }

    public function testFinderShouldFilterByInterfaceImplementation()
    {
        $finder = new ComposerFinder();
        $finder->in([__DIR__.'/../../data']);
        $finder->implementationOf(Psr4\FooInterface::class);

        self::assertEquals([
            Psr4\BarBar::class => new \ReflectionClass(Psr4\BarBar::class),
            Psr0\BarBar::class => new \ReflectionClass(Psr0\BarBar::class),
        ], \iterator_to_array($finder));
    }

    public function testFinderShouldFilterBySuperClass()
    {
        $finder = new ComposerFinder();
        $finder->in([__DIR__.'/../../data']);
        $finder->subclassOf(Psr4\AbstractClass::class);

        self::assertEquals([
            Psr4\Foobar::class => new \ReflectionClass(Psr4\Foobar::class),
            Psr0\Foobar::class => new \ReflectionClass(Psr0\Foobar::class),
        ], \iterator_to_array($finder));
    }

    public function testFinderShouldFilterByAnnotation()
    {
        $finder = new ComposerFinder();
        $finder->in([__DIR__.'/../../data']);
        $finder->annotatedBy(Psr4\SubNs\FooBaz::class);

        self::assertEquals([
            Psr4\AbstractClass::class => new \ReflectionClass(Psr4\AbstractClass::class),
            Psr0\SubNs\FooBaz::class => new \ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], \iterator_to_array($finder));
    }

    public function testFinderShouldFilterByCallback()
    {
        $finder = new ComposerFinder();
        $finder->in([__DIR__.'/../../data']);
        $finder->filter(function (\ReflectionClass $class) {
            return Psr4\AbstractClass::class === $class->getName();
        });

        self::assertEquals([
            Psr4\AbstractClass::class => new \ReflectionClass(Psr4\AbstractClass::class),
        ], \iterator_to_array($finder));
    }

    public function testFinderShouldFilterByPath()
    {
        $finder = new ComposerFinder();
        $finder->in([__DIR__.'/../../data']);
        $finder->path('SubNs');

        self::assertEquals([
            Psr4\SubNs\FooBaz::class => new \ReflectionClass(Psr4\SubNs\FooBaz::class),
            Psr0\SubNs\FooBaz::class => new \ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], \iterator_to_array($finder));
    }

    public function testFinderShouldFilterByPathRegex()
    {
        $finder = new ComposerFinder();
        $finder->in([__DIR__.'/../../data']);
        $finder->path('/subns/i');

        self::assertEquals([
            Psr4\SubNs\FooBaz::class => new \ReflectionClass(Psr4\SubNs\FooBaz::class),
            Psr0\SubNs\FooBaz::class => new \ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], \iterator_to_array($finder));
    }

    public function testFinderShouldFilterByNotPath()
    {
        $finder = new ComposerFinder();
        $finder->in([__DIR__.'/../../data']);
        $finder->notPath('SubNs');

        self::assertEquals([
            Psr4\BarBar::class => new \ReflectionClass(Psr4\BarBar::class),
            Psr4\Foobar::class => new \ReflectionClass(Psr4\Foobar::class),
            Psr4\AbstractClass::class => new \ReflectionClass(Psr4\AbstractClass::class),
            Psr4\FooInterface::class => new \ReflectionClass(Psr4\FooInterface::class),
            Psr4\FooTrait::class => new \ReflectionClass(Psr4\FooTrait::class),
            Psr0\BarBar::class => new \ReflectionClass(Psr0\BarBar::class),
            Psr0\Foobar::class => new \ReflectionClass(Psr0\Foobar::class),
            Psr4WithClassMap\BarBar::class => new \ReflectionClass(Psr4WithClassMap\BarBar::class),
        ], \iterator_to_array($finder));
    }

    public function testFinderShouldFilterByNotPathRegex()
    {
        $finder = new ComposerFinder();
        $finder->in([__DIR__.'/../../data']);
        $finder->notPath('/subns/i');

        self::assertEquals([
            Psr4\BarBar::class => new \ReflectionClass(Psr4\BarBar::class),
            Psr4\Foobar::class => new \ReflectionClass(Psr4\Foobar::class),
            Psr4\AbstractClass::class => new \ReflectionClass(Psr4\AbstractClass::class),
            Psr4\FooInterface::class => new \ReflectionClass(Psr4\FooInterface::class),
            Psr4\FooTrait::class => new \ReflectionClass(Psr4\FooTrait::class),
            Psr0\BarBar::class => new \ReflectionClass(Psr0\BarBar::class),
            Psr0\Foobar::class => new \ReflectionClass(Psr0\Foobar::class),
            Psr4WithClassMap\BarBar::class => new \ReflectionClass(Psr4WithClassMap\BarBar::class),
        ], \iterator_to_array($finder));
    }
}
