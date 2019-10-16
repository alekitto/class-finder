<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\Finder;

use Kcs\ClassFinder\Finder\PhpDocumentorFinder;
use Kcs\ClassFinder\Fixtures\Psr0;
use Kcs\ClassFinder\Fixtures\Psr4;
use phpDocumentor\Reflection\BaseReflector;
use phpDocumentor\Reflection\ClassReflector;
use phpDocumentor\Reflection\InterfaceReflector;
use phpDocumentor\Reflection\TraitReflector;
use PHPUnit\Framework\TestCase;

class PhpDocumentorFinderTest extends TestCase
{
    public function testFinderShouldBeIterable()
    {
        $finder = new PhpDocumentorFinder(__DIR__);
        self::assertInstanceOf(\Traversable::class, $finder);
    }

    public function testFinderShouldFilterByNamespace()
    {
        $finder = new PhpDocumentorFinder(__DIR__.'/../../data');
        $finder->inNamespace(['Kcs\ClassFinder\Fixtures\Psr4']);

        $classes = \iterator_to_array($finder);

        self::assertArrayHasKey(Psr4\BarBar::class, $classes);
        self::assertInstanceOf(ClassReflector::class, $classes[Psr4\BarBar::class]);
        self::assertArrayHasKey(Psr4\Foobar::class, $classes);
        self::assertInstanceOf(ClassReflector::class, $classes[Psr4\Foobar::class]);
        self::assertArrayHasKey(Psr4\AbstractClass::class, $classes);
        self::assertInstanceOf(ClassReflector::class, $classes[Psr4\AbstractClass::class]);
        self::assertArrayHasKey(Psr4\SubNs\FooBaz::class, $classes);
        self::assertInstanceOf(ClassReflector::class, $classes[Psr4\SubNs\FooBaz::class]);
        self::assertArrayHasKey(Psr4\FooInterface::class, $classes);
        self::assertInstanceOf(InterfaceReflector::class, $classes[Psr4\FooInterface::class]);
        self::assertArrayHasKey(Psr4\FooTrait::class, $classes);
        self::assertInstanceOf(TraitReflector::class, $classes[Psr4\FooTrait::class]);
    }

    public function testFinderShouldFilterByDirectory()
    {
        $finder = new PhpDocumentorFinder(__DIR__.'/../../data');
        $finder->in([__DIR__.'/../../data/Composer/Psr0']);

        $classes = \iterator_to_array($finder);

        self::assertArrayHasKey(Psr0\BarBar::class, $classes);
        self::assertInstanceOf(ClassReflector::class, $classes[Psr0\BarBar::class]);
        self::assertArrayHasKey(Psr0\Foobar::class, $classes);
        self::assertInstanceOf(ClassReflector::class, $classes[Psr0\Foobar::class]);
        self::assertArrayHasKey(Psr0\SubNs\FooBaz::class, $classes);
        self::assertInstanceOf(ClassReflector::class, $classes[Psr0\SubNs\FooBaz::class]);
    }

    public function testFinderShouldFilterByInterfaceImplementation()
    {
        $finder = new PhpDocumentorFinder(__DIR__.'/../../data');
        $finder->in([__DIR__.'/../../data']);
        $finder->implementationOf(Psr4\FooInterface::class);

        $classes = \iterator_to_array($finder);

        self::assertArrayHasKey(Psr4\BarBar::class, $classes);
        self::assertInstanceOf(ClassReflector::class, $classes[Psr4\BarBar::class]);
    }

    public function testFinderShouldFilterBySuperClass()
    {
        $finder = new PhpDocumentorFinder(__DIR__.'/../../data');
        $finder->in([__DIR__.'/../../data']);
        $finder->subclassOf(Psr4\AbstractClass::class);

        $classes = \iterator_to_array($finder);

        self::assertArrayHasKey(Psr4\Foobar::class, $classes);
        self::assertInstanceOf(ClassReflector::class, $classes[Psr4\Foobar::class]);
    }

    public function testFinderShouldFilterByAnnotation()
    {
        $finder = new PhpDocumentorFinder(__DIR__.'/../../data');
        $finder->in([__DIR__.'/../../data']);
        $finder->annotatedBy(Psr4\SubNs\FooBaz::class);

        $classes = \iterator_to_array($finder);

        self::assertArrayHasKey(Psr4\AbstractClass::class, $classes);
        self::assertInstanceOf(ClassReflector::class, $classes[Psr4\AbstractClass::class]);
    }

    public function testFinderShouldFilterByCallback()
    {
        $finder = new PhpDocumentorFinder(__DIR__.'/../../data');
        $finder->filter(function (BaseReflector $class) {
            return $class->getName() === '\\'.Psr4\AbstractClass::class;
        });

        $classes = \iterator_to_array($finder);

        self::assertCount(1, $classes);
        self::assertArrayHasKey(Psr4\AbstractClass::class, $classes);
        self::assertInstanceOf(ClassReflector::class, $classes[Psr4\AbstractClass::class]);
    }
}
