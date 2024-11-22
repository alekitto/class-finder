<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\unit\Finder;

use FallbackNamespace0;
use Kcs\ClassFinder\FileFinder\FileFinderInterface;
use Kcs\ClassFinder\Finder\RecursiveFinder;
use Kcs\ClassFinder\Fixtures\Psr0;
use Kcs\ClassFinder\Fixtures\Psr4;
use Kcs\ClassFinder\Fixtures\Recursive;
use Logger;
use LoggerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SplFileInfo;
use Traversable;

use function basename;
use function iterator_to_array;
use function realpath;
use function str_starts_with;

class RecursiveFinderTest extends TestCase
{
    public function testFinderShouldBeIterable(): void
    {
        $finder = new RecursiveFinder(__DIR__);
        self::assertInstanceOf(Traversable::class, $finder);
    }

    public function testFinderShouldFilterByNamespace(): void
    {
        $finder = new RecursiveFinder(__DIR__ . '/../../../data/Composer');
        $finder->inNamespace(['Kcs\ClassFinder\Fixtures\Psr4']);

        $classes = iterator_to_array($finder);

        self::assertEquals([
            Psr4\BarBar::class => new ReflectionClass(Psr4\BarBar::class),
            Psr4\Foobar::class => new ReflectionClass(Psr4\Foobar::class),
            Psr4\AbstractClass::class => new ReflectionClass(Psr4\AbstractClass::class),
            Psr4\FooInterface::class => new ReflectionClass(Psr4\FooInterface::class),
            Psr4\FooTrait::class => new ReflectionClass(Psr4\FooTrait::class),
            Psr4\HiddenClass::class => new ReflectionClass(Psr4\HiddenClass::class),
            Psr4\SubNs\FooBaz::class => new ReflectionClass(Psr4\SubNs\FooBaz::class),
            Psr4\Foobarbar::class => new ReflectionClass(Psr4\Foobarbar::class),
        ], $classes);
    }

    public function testFinderShouldFilterByDirectory(): void
    {
        $finder = new RecursiveFinder(__DIR__ . '/../../../data/Composer');
        $finder->in([__DIR__ . '/../../../data/Composer/Psr0']);

        self::assertEquals([
            Psr0\BarBar::class => new ReflectionClass(Psr0\BarBar::class),
            Psr0\Foobar::class => new ReflectionClass(Psr0\Foobar::class),
            Psr0\SubNs\FooBaz::class => new ReflectionClass(Psr0\SubNs\FooBaz::class),
            Logger::class => new ReflectionClass(Logger::class),
            LoggerInterface::class => new ReflectionClass(LoggerInterface::class),
            FallbackNamespace0\MyClass::class => new ReflectionClass(FallbackNamespace0\MyClass::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByInterfaceImplementation(): void
    {
        $finder = new RecursiveFinder(__DIR__ . '/../../../data/Composer');
        $finder->implementationOf(Psr4\FooInterface::class);

        self::assertEquals([
            Psr4\BarBar::class => new ReflectionClass(Psr4\BarBar::class),
            Psr0\BarBar::class => new ReflectionClass(Psr0\BarBar::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterBySuperClass(): void
    {
        $finder = new RecursiveFinder(__DIR__ . '/../../../data/Composer');
        $finder->subclassOf(Psr4\AbstractClass::class);

        self::assertEquals([
            Psr4\Foobar::class => new ReflectionClass(Psr4\Foobar::class),
            Psr0\Foobar::class => new ReflectionClass(Psr0\Foobar::class),
            Psr4\Foobarbar::class => new ReflectionClass(Psr4\Foobarbar::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByAnnotation(): void
    {
        $finder = new RecursiveFinder(__DIR__ . '/../../../data/Composer');
        $finder->annotatedBy(Psr4\SubNs\FooBaz::class);

        self::assertEquals([
            Psr4\AbstractClass::class => new ReflectionClass(Psr4\AbstractClass::class),
            Psr0\SubNs\FooBaz::class => new ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByAttribute(): void
    {
        $finder = new RecursiveFinder(__DIR__ . '/../../../data/Composer');
        $finder->withAttribute(Psr4\SubNs\FooBaz::class);

        self::assertEquals([
            Psr4\AbstractClass::class => new ReflectionClass(Psr4\AbstractClass::class),
            Psr0\SubNs\FooBaz::class => new ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByCallback(): void
    {
        $finder = new RecursiveFinder(__DIR__ . '/../../../data/Composer');
        $finder->filter(static function (ReflectionClass $class) {
            return $class->getName() === Psr4\AbstractClass::class;
        });

        self::assertEquals([
            Psr4\AbstractClass::class => new ReflectionClass(Psr4\AbstractClass::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByIteratorCallback(): void
    {
        $finder = new RecursiveFinder(__DIR__ . '/../../../data/Recursive');
        $finder->pathFilter(static function (string $path): bool {
            return ! str_starts_with(basename($path), 'class-');
        });

        $classes = iterator_to_array($finder);

        self::assertEquals([
            Recursive\Bar::class => new ReflectionClass(Recursive\Bar::class),
            Recursive\Foo::class => new ReflectionClass(Recursive\Foo::class),
        ], $classes);
    }

    public function testFinderShouldUseGivenFileFinder(): void
    {
        $finder = new RecursiveFinder(__DIR__ . '/../../../data/Recursive');
        $fileFinder = $this->createMock(FileFinderInterface::class);
        $finder->withFileFinder($fileFinder);

        $fileFinder->expects(self::once())
            ->method('search')
            ->willReturn([
                realpath(__DIR__ . '/../../../data/Recursive/Foo.php') => new SplFileInfo(realpath(__DIR__ . '/../../../data/Recursive/Foo.php')),
                realpath(__DIR__ . '/../../../data/Recursive/class-foo-bar.php') => new SplFileInfo(realpath(__DIR__ . '/../../../data/Recursive/class-foo-bar.php')),
            ]);

        $classes = iterator_to_array($finder);

        self::assertEquals([
            Recursive\Foo::class => new ReflectionClass(Recursive\Foo::class),
            Recursive\FooBar::class => new ReflectionClass(Recursive\FooBar::class),
        ], $classes);
    }
}
