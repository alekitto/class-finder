<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\unit\Finder;

use Kcs\ClassFinder\Finder\ComposerFinder;
use Kcs\ClassFinder\Fixtures\Psr0;
use Kcs\ClassFinder\Fixtures\Psr4;
use Kcs\ClassFinder\Fixtures\Psr4WithClassMap;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\ErrorHandler\DebugClassLoader as ErrorHandlerClassLoader;
use Traversable;
use function iterator_to_array;

class ComposerFinderTest extends TestCase
{
    public function testFinderShouldBeIterable(): void
    {
        $finder = new ComposerFinder();
        self::assertInstanceOf(Traversable::class, $finder);
    }

    public function testShouldNotThrowWhenSymfonyErrorHandlerClassLoaderIsEnabled(): void
    {
        ErrorHandlerClassLoader::enable();

        try {
            $finder = new ComposerFinder();
            self::assertInstanceOf(ComposerFinder::class, $finder);
        } finally {
            ErrorHandlerClassLoader::disable();
        }
    }

    public function testFinderShouldFilterByNamespace(): void
    {
        $finder = (new ComposerFinder())->useAutoloading(false);
        $finder->inNamespace(['Kcs\ClassFinder\Fixtures\Psr4']);

        self::assertEquals([
            Psr4\BarBar::class => new ReflectionClass(Psr4\BarBar::class),
            Psr4\Foobar::class => new ReflectionClass(Psr4\Foobar::class),
            Psr4\AbstractClass::class => new ReflectionClass(Psr4\AbstractClass::class),
            Psr4\FooInterface::class => new ReflectionClass(Psr4\FooInterface::class),
            Psr4\FooTrait::class => new ReflectionClass(Psr4\FooTrait::class),
            Psr4\SubNs\FooBaz::class => new ReflectionClass(Psr4\SubNs\FooBaz::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByExcludedNamespace(): void
    {
        $finder = new ComposerFinder();
        $finder
            ->inNamespace(['Kcs\ClassFinder\Fixtures'])
            ->notInNamespace(['Kcs\ClassFinder\Fixtures\Psr4']);

        self::assertEquals([
            Psr0\BarBar::class => new ReflectionClass(Psr0\BarBar::class),
            Psr0\Foobar::class => new ReflectionClass(Psr0\Foobar::class),
            Psr0\SubNs\FooBaz::class => new ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByDirectory(): void
    {
        $finder = new ComposerFinder();
        $finder->in([__DIR__ . '/../../../data/Composer/Psr0']);

        self::assertEquals([
            Psr0\BarBar::class => new ReflectionClass(Psr0\BarBar::class),
            Psr0\Foobar::class => new ReflectionClass(Psr0\Foobar::class),
            Psr0\SubNs\FooBaz::class => new ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterBySubDirectory(): void
    {
        $finder = (new ComposerFinder())->useAutoloading(false);
        $finder->in([__DIR__ . '/../../../data/Composer/Psr4/SubNs']);

        self::assertEquals([
            Psr4\SubNs\FooBaz::class => new ReflectionClass(Psr4\SubNs\FooBaz::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByInterfaceImplementation(): void
    {
        $finder = (new ComposerFinder())->useAutoloading(false);
        $finder->in([__DIR__ . '/../../../data']);
        $finder->implementationOf(Psr4\FooInterface::class);

        self::assertEquals([
            Psr4\BarBar::class => new ReflectionClass(Psr4\BarBar::class),
            Psr0\BarBar::class => new ReflectionClass(Psr0\BarBar::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterBySuperClass(): void
    {
        $finder = (new ComposerFinder())->useAutoloading(false);
        $finder->in([__DIR__ . '/../../../data']);
        $finder->subclassOf(Psr4\AbstractClass::class);

        self::assertEquals([
            Psr4\Foobar::class => new ReflectionClass(Psr4\Foobar::class),
            Psr0\Foobar::class => new ReflectionClass(Psr0\Foobar::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByAnnotation(): void
    {
        $finder = (new ComposerFinder())->useAutoloading(false);
        $finder->in([__DIR__ . '/../../../data']);
        $finder->annotatedBy(Psr4\SubNs\FooBaz::class);

        self::assertEquals([
            Psr4\AbstractClass::class => new ReflectionClass(Psr4\AbstractClass::class),
            Psr0\SubNs\FooBaz::class => new ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByAttribute(): void
    {
        $finder = (new ComposerFinder())->useAutoloading(false);
        $finder->in([__DIR__ . '/../../../data']);
        $finder->withAttribute(Psr4\SubNs\FooBaz::class);

        self::assertEquals([
            Psr4\AbstractClass::class => new ReflectionClass(Psr4\AbstractClass::class),
            Psr0\SubNs\FooBaz::class => new ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByCallback(): void
    {
        $finder = (new ComposerFinder())->useAutoloading(false);
        $finder->in([__DIR__ . '/../../../data']);
        $finder->filter(static function (ReflectionClass $class) {
            return $class->getName() === Psr4\AbstractClass::class;
        });

        self::assertEquals([
            Psr4\AbstractClass::class => new ReflectionClass(Psr4\AbstractClass::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByPath(): void
    {
        $finder = (new ComposerFinder())->useAutoloading(false);
        $finder->in([__DIR__ . '/../../../data']);
        $finder->path('SubNs');

        self::assertEquals([
            Psr4\SubNs\FooBaz::class => new ReflectionClass(Psr4\SubNs\FooBaz::class),
            Psr0\SubNs\FooBaz::class => new ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByPathRegex(): void
    {
        $finder = (new ComposerFinder())->useAutoloading(false);
        $finder->in([__DIR__ . '/../../../data']);
        $finder->path('/subns/i');

        self::assertEquals([
            Psr4\SubNs\FooBaz::class => new ReflectionClass(Psr4\SubNs\FooBaz::class),
            Psr0\SubNs\FooBaz::class => new ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByNotPath(): void
    {
        $finder = (new ComposerFinder())->useAutoloading(false);
        $finder->in([__DIR__ . '/../../../data']);
        $finder->notPath('SubNs');

        self::assertEquals([
            Psr4\BarBar::class => new ReflectionClass(Psr4\BarBar::class),
            Psr4\Foobar::class => new ReflectionClass(Psr4\Foobar::class),
            Psr4\AbstractClass::class => new ReflectionClass(Psr4\AbstractClass::class),
            Psr4\FooInterface::class => new ReflectionClass(Psr4\FooInterface::class),
            Psr4\FooTrait::class => new ReflectionClass(Psr4\FooTrait::class),
            Psr0\BarBar::class => new ReflectionClass(Psr0\BarBar::class),
            Psr0\Foobar::class => new ReflectionClass(Psr0\Foobar::class),
            Psr4WithClassMap\BarBar::class => new ReflectionClass(Psr4WithClassMap\BarBar::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByNotPathRegex(): void
    {
        $finder = (new ComposerFinder())->useAutoloading(false);
        $finder->in([__DIR__ . '/../../../data']);
        $finder->notPath('/subns/i');

        self::assertEquals([
            Psr4\BarBar::class => new ReflectionClass(Psr4\BarBar::class),
            Psr4\Foobar::class => new ReflectionClass(Psr4\Foobar::class),
            Psr4\AbstractClass::class => new ReflectionClass(Psr4\AbstractClass::class),
            Psr4\FooInterface::class => new ReflectionClass(Psr4\FooInterface::class),
            Psr4\FooTrait::class => new ReflectionClass(Psr4\FooTrait::class),
            Psr0\BarBar::class => new ReflectionClass(Psr0\BarBar::class),
            Psr0\Foobar::class => new ReflectionClass(Psr0\Foobar::class),
            Psr4WithClassMap\BarBar::class => new ReflectionClass(Psr4WithClassMap\BarBar::class),
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByPathCallback(): void
    {
        $finder = (new ComposerFinder())->useAutoloading(false);
        $finder->in([__DIR__ . '/../../../data']);
        $finder->pathFilter(static fn (string $path): bool => !str_ends_with($path, 'BarBar.php'));

        self::assertEquals([
            Psr4\Foobar::class => new ReflectionClass(Psr4\Foobar::class),
            Psr4\AbstractClass::class => new ReflectionClass(Psr4\AbstractClass::class),
            Psr4\FooInterface::class => new ReflectionClass(Psr4\FooInterface::class),
            Psr4\FooTrait::class => new ReflectionClass(Psr4\FooTrait::class),
            Psr0\Foobar::class => new ReflectionClass(Psr0\Foobar::class),
            Psr4\SubNs\FooBaz::class => new ReflectionClass(Psr4\SubNs\FooBaz::class),
            Psr0\SubNs\FooBaz::class => new ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], iterator_to_array($finder));
    }
}
