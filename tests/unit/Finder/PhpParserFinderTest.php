<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\unit\Finder;

use Kcs\ClassFinder\Finder\PhpParserFinder;
use Kcs\ClassFinder\Fixtures\Psr0;
use Kcs\ClassFinder\Fixtures\Psr4;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PHPUnit\Framework\TestCase;
use Traversable;

use function iterator_to_array;
use function str_ends_with;

class PhpParserFinderTest extends TestCase
{
    public function testFinderShouldBeIterable(): void
    {
        $finder = new PhpParserFinder(__DIR__);
        self::assertInstanceOf(Traversable::class, $finder);
    }

    public function testFinderShouldFilterByNamespace(): void
    {
        $finder = new PhpParserFinder(__DIR__ . '/../../../data');
        $finder->inNamespace(['Kcs\ClassFinder\Fixtures\Psr4']);

        $classes = iterator_to_array($finder);

        self::assertCount(8, $classes);
        self::assertArrayHasKey(Psr4\BarBar::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr4\BarBar::class]);
        self::assertArrayHasKey(Psr4\Foobar::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr4\Foobar::class]);
        self::assertArrayHasKey(Psr4\Foobarbar::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr4\Foobarbar::class]);
        self::assertArrayHasKey(Psr4\AbstractClass::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr4\AbstractClass::class]);
        self::assertArrayHasKey(Psr4\HiddenClass::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr4\HiddenClass::class]);
        self::assertArrayHasKey(Psr4\SubNs\FooBaz::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr4\SubNs\FooBaz::class]);
        self::assertArrayHasKey(Psr4\FooInterface::class, $classes);
        self::assertInstanceOf(Interface_::class, $classes[Psr4\FooInterface::class]);
        self::assertArrayHasKey(Psr4\FooTrait::class, $classes);
        self::assertInstanceOf(Trait_::class, $classes[Psr4\FooTrait::class]);
    }

    public function testFinderShouldFilterByExcludedNamespace(): void
    {
        $finder = new PhpParserFinder(__DIR__ . '/../../../data');
        $finder
            ->inNamespace(['Kcs\ClassFinder\Fixtures'])
            ->notInNamespace([
                'Kcs\ClassFinder\Fixtures\Recursive',
                'Kcs\ClassFinder\Fixtures\Psr4',
                'Kcs\ClassFinder\Fixtures\Psr4WithClassMap',
            ]);

        $classes = iterator_to_array($finder);

        self::assertCount(3, $classes);
        self::assertArrayHasKey(Psr0\BarBar::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr0\BarBar::class]);
        self::assertArrayHasKey(Psr0\Foobar::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr0\Foobar::class]);
        self::assertArrayHasKey(Psr0\SubNs\FooBaz::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr0\SubNs\FooBaz::class]);
    }

    public function testFinderShouldFilterByDirectory(): void
    {
        $finder = new PhpParserFinder(__DIR__ . '/../../../data');
        $finder->in([__DIR__ . '/../../../data/Composer/Psr0']);

        $classes = iterator_to_array($finder);

        self::assertArrayHasKey(Psr0\BarBar::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr0\BarBar::class]);
        self::assertArrayHasKey(Psr0\Foobar::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr0\Foobar::class]);
        self::assertArrayHasKey(Psr0\SubNs\FooBaz::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr0\SubNs\FooBaz::class]);
    }

    public function testFinderShouldFilterByInterfaceImplementation(): void
    {
        $finder = new PhpParserFinder(__DIR__ . '/../../../data');
        $finder->in([__DIR__ . '/../../../data']);
        $finder->implementationOf(Psr4\FooInterface::class);

        $classes = iterator_to_array($finder);

        self::assertArrayHasKey(Psr4\BarBar::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr4\BarBar::class]);
    }

    public function testFinderShouldFilterBySuperClass(): void
    {
        $finder = new PhpParserFinder(__DIR__ . '/../../../data');
        $finder->in([__DIR__ . '/../../../data']);
        $finder->subclassOf(Psr4\AbstractClass::class);

        $classes = iterator_to_array($finder);

        self::assertArrayHasKey(Psr4\Foobar::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr4\Foobar::class]);
        self::assertArrayHasKey(Psr4\Foobarbar::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr4\Foobarbar::class]);
    }

    public function testFinderShouldFilterByAnnotation(): void
    {
        $finder = new PhpParserFinder(__DIR__ . '/../../../data');
        $finder->in([__DIR__ . '/../../../data']);
        $finder->annotatedBy(Psr4\SubNs\FooBaz::class);

        $classes = iterator_to_array($finder);

        self::assertArrayHasKey(Psr4\AbstractClass::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr4\AbstractClass::class]);
    }

    public function testFinderShouldFilterByAttribute(): void
    {
        $finder = new PhpParserFinder(__DIR__ . '/../../../data');
        $finder->in([__DIR__ . '/../../../data']);
        $finder->withAttribute(Psr4\SubNs\FooBaz::class);

        $classes = iterator_to_array($finder);

        self::assertArrayHasKey(Psr4\AbstractClass::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr4\AbstractClass::class]);
    }

    public function testFinderShouldFilterByCallback(): void
    {
        $finder = new PhpParserFinder(__DIR__ . '/../../../data');
        $finder->filter(static function (ClassLike $class) {
            return $class->namespacedName->toString() === Psr4\AbstractClass::class;
        });

        $classes = iterator_to_array($finder);

        self::assertCount(1, $classes);
        self::assertArrayHasKey(Psr4\AbstractClass::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr4\AbstractClass::class]);
    }

    public function testFinderShouldFilterByPathCallback(): void
    {
        $finder = new PhpParserFinder(__DIR__ . '/../../../data');
        $finder->in([__DIR__ . '/../../../data/Composer/Psr?']);
        $finder->pathFilter(static fn (string $path): bool => ! str_ends_with($path, 'BarBar.php'));

        $classes = iterator_to_array($finder);

        self::assertCount(14, $classes);
        self::assertArrayHasKey(Psr4\AbstractClass::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr4\AbstractClass::class]);
        self::assertArrayHasKey(Psr0\Foobar::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr0\Foobar::class]);
        self::assertArrayHasKey(Psr0\SubNs\FooBaz::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr0\SubNs\FooBaz::class]);
    }
}
