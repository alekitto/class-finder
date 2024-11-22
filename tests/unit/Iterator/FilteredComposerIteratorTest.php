<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\unit\Iterator;

use Closure;
use Composer\Autoload\ClassLoader;
use Kcs\ClassFinder\Fixtures\Psr0;
use Kcs\ClassFinder\Fixtures\Psr4;
use Kcs\ClassFinder\Iterator\FilteredComposerIterator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function iterator_to_array;
use function str_ends_with;

class FilteredComposerIteratorTest extends TestCase
{
    private ClassLoader $loader;

    protected function setUp(): void
    {
        $loader = new ClassLoader();
        (Closure::bind(static function () use ($loader): void {
            $loader->classMap = [__CLASS__ => __FILE__]; // phpcs:ignore

            $loader->prefixDirsPsr4 = [
                'Kcs\\ClassFinder\\Fixtures\\Psr4\\' => [
                    __DIR__ . '/../../..' . '/data/Composer/Psr4',
                ],
            ];

            $loader->prefixesPsr0 = [
                'K' => [
                    'Kcs\\ClassFinder\\Fixtures\\Psr0\\' => [
                        __DIR__ . '/../../..' . '/data/Composer/Psr0',
                    ],
                ],
            ];
        }, null, ClassLoader::class))();

        $this->loader = $loader;
    }

    public function testComposerIteratorShouldWork(): void
    {
        $iterator = new FilteredComposerIterator($this->loader, null, null, null, null);

        self::assertEquals([
            self::class => new ReflectionClass(self::class),
            Psr4\BarBar::class => new ReflectionClass(Psr4\BarBar::class),
            Psr4\Foobar::class => new ReflectionClass(Psr4\Foobar::class),
            Psr4\AbstractClass::class => new ReflectionClass(Psr4\AbstractClass::class),
            Psr4\FooInterface::class => new ReflectionClass(Psr4\FooInterface::class),
            Psr4\FooTrait::class => new ReflectionClass(Psr4\FooTrait::class),
            Psr4\SubNs\FooBaz::class => new ReflectionClass(Psr4\SubNs\FooBaz::class),
            Psr0\BarBar::class => new ReflectionClass(Psr0\BarBar::class),
            Psr0\Foobar::class => new ReflectionClass(Psr0\Foobar::class),
            Psr0\SubNs\FooBaz::class => new ReflectionClass(Psr0\SubNs\FooBaz::class),
            Psr4\Foobarbar::class => new ReflectionClass(Psr4\Foobarbar::class),
        ], iterator_to_array($iterator));
    }

    public function testComposerIteratorShouldCallPathCallback(): void
    {
        $iterator = new FilteredComposerIterator($this->loader, null, null, null, null, 0, static function (string $path): bool {
            return ! str_ends_with($path, 'BarBar.php');
        });

        self::assertEquals([
            self::class => new ReflectionClass(self::class),
            Psr4\Foobar::class => new ReflectionClass(Psr4\Foobar::class),
            Psr4\AbstractClass::class => new ReflectionClass(Psr4\AbstractClass::class),
            Psr4\FooInterface::class => new ReflectionClass(Psr4\FooInterface::class),
            Psr4\FooTrait::class => new ReflectionClass(Psr4\FooTrait::class),
            Psr4\SubNs\FooBaz::class => new ReflectionClass(Psr4\SubNs\FooBaz::class),
            Psr0\Foobar::class => new ReflectionClass(Psr0\Foobar::class),
            Psr0\SubNs\FooBaz::class => new ReflectionClass(Psr0\SubNs\FooBaz::class),
            Psr4\Foobarbar::class => new ReflectionClass(Psr4\Foobarbar::class),
        ], iterator_to_array($iterator));
    }

    public function testComposerIteratorShouldFilterNotIntersectingPath(): void
    {
        // NOTE: This test could be interpreted as wrong, but is not:
        // the purpose of the FilteredComposerIterator class is to do some *quick and dirty* filtering
        // not to be precise enough to be used directly. In this case the Psr4/ direct children
        // intersects perfectly with the requested dirs. The upper finder should filter out the
        // non-matching results.

        $iterator = new FilteredComposerIterator($this->loader, null, null, null, [__DIR__ . '/../../..' . '/data/Composer/Psr4/SubNs']);

        self::assertEquals([
            Psr4\BarBar::class => new ReflectionClass(Psr4\BarBar::class),
            Psr4\Foobar::class => new ReflectionClass(Psr4\Foobar::class),
            Psr4\AbstractClass::class => new ReflectionClass(Psr4\AbstractClass::class),
            Psr4\FooInterface::class => new ReflectionClass(Psr4\FooInterface::class),
            Psr4\FooTrait::class => new ReflectionClass(Psr4\FooTrait::class),
            Psr4\SubNs\FooBaz::class => new ReflectionClass(Psr4\SubNs\FooBaz::class),
            Psr4\Foobarbar::class => new ReflectionClass(Psr4\Foobarbar::class),
        ], iterator_to_array($iterator));
    }
}
