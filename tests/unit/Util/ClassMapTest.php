<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\unit\Util;

use Kcs\ClassFinder\Finder\Psr4Finder;
use Kcs\ClassFinder\Fixtures\Psr4;
use Kcs\ClassFinder\PathNormalizer;
use Kcs\ClassFinder\Util\ClassMap;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function iterator_to_array;
use function realpath;

class ClassMapTest extends TestCase
{
    public function testCreateFromFinder(): void
    {
        $finder = new Psr4Finder('Kcs\ClassFinder\Fixtures\Psr4', __DIR__ . '/../../../data/Composer/Psr4');
        $classMap = ClassMap::fromFinder($finder);

        self::assertEquals([
            Psr4\BarBar::class => new ReflectionClass(Psr4\BarBar::class),
            Psr4\Foobar::class => new ReflectionClass(Psr4\Foobar::class),
            Psr4\AbstractClass::class => new ReflectionClass(Psr4\AbstractClass::class),
            Psr4\FooInterface::class => new ReflectionClass(Psr4\FooInterface::class),
            Psr4\FooTrait::class => new ReflectionClass(Psr4\FooTrait::class),
            Psr4\SubNs\FooBaz::class => new ReflectionClass(Psr4\SubNs\FooBaz::class),
            Psr4\Foobarbar::class => new ReflectionClass(Psr4\Foobarbar::class),
        ], iterator_to_array($classMap));
    }

    public function testCreateFinder(): void
    {
        $finder = new Psr4Finder('Kcs\ClassFinder\Fixtures\Psr4', __DIR__ . '/../../../data/Composer/Psr4');
        $classMap = ClassMap::fromFinder($finder);

        $finder = $classMap->createFinder()->skipNonInstantiable();

        self::assertEquals([
            Psr4\BarBar::class => new ReflectionClass(Psr4\BarBar::class),
            Psr4\Foobar::class => new ReflectionClass(Psr4\Foobar::class),
            Psr4\SubNs\FooBaz::class => new ReflectionClass(Psr4\SubNs\FooBaz::class),
            Psr4\Foobarbar::class => new ReflectionClass(Psr4\Foobarbar::class),
        ], iterator_to_array($finder));
    }

    public function testGetMap(): void
    {
        $finder = new Psr4Finder('Kcs\ClassFinder\Fixtures\Psr4', __DIR__ . '/../../../data/Composer/Psr4');
        $classMap = ClassMap::fromFinder($finder);

        self::assertEquals([
            Psr4\BarBar::class => realpath(__DIR__ . '/../../../data/Composer/Psr4/BarBar.php'),
            Psr4\Foobar::class => realpath(__DIR__ . '/../../../data/Composer/Psr4/Foobar.php'),
            Psr4\AbstractClass::class => realpath(__DIR__ . '/../../../data/Composer/Psr4/AbstractClass.php'),
            Psr4\FooInterface::class => realpath(__DIR__ . '/../../../data/Composer/Psr4/FooInterface.php'),
            Psr4\FooTrait::class => realpath(__DIR__ . '/../../../data/Composer/Psr4/FooTrait.php'),
            Psr4\SubNs\FooBaz::class => realpath(__DIR__ . '/../../../data/Composer/Psr4/SubNs/FooBaz.php'),
            Psr4\Foobarbar::class => realpath(__DIR__ . '/../../../data/Composer/Psr4/Foobarbar.php'),
        ], $classMap->getMap());
    }

    public function testGetMapRelative(): void
    {
        $finder = new Psr4Finder('Kcs\ClassFinder\Fixtures\Psr4', __DIR__ . '/../../../data/Composer/Psr4');
        $classMap = ClassMap::fromFinder($finder);

        self::assertEquals([
            Psr4\BarBar::class => PathNormalizer::resolvePath('data/Composer/Psr4/BarBar.php'),
            Psr4\Foobar::class => PathNormalizer::resolvePath('data/Composer/Psr4/Foobar.php'),
            Psr4\AbstractClass::class => PathNormalizer::resolvePath('data/Composer/Psr4/AbstractClass.php'),
            Psr4\FooInterface::class => PathNormalizer::resolvePath('data/Composer/Psr4/FooInterface.php'),
            Psr4\FooTrait::class => PathNormalizer::resolvePath('data/Composer/Psr4/FooTrait.php'),
            Psr4\SubNs\FooBaz::class => PathNormalizer::resolvePath('data/Composer/Psr4/SubNs/FooBaz.php'),
            Psr4\Foobarbar::class => PathNormalizer::resolvePath('data/Composer/Psr4/Foobarbar.php'),
        ], $classMap->getMap(__DIR__ . '/../../../'));
    }
}
