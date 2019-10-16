<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\Iterator;

use Kcs\ClassFinder\Fixtures\Psr4;
use Kcs\ClassFinder\Iterator\PhpDocumentorIterator;
use phpDocumentor\Reflection\ClassReflector;
use phpDocumentor\Reflection\InterfaceReflector;
use phpDocumentor\Reflection\TraitReflector;
use PHPUnit\Framework\TestCase;

class PhpDocumentorIteratorTest extends TestCase
{
    public function testIteratorShouldWork()
    {
        $iterator = new PhpDocumentorIterator(
            \realpath(__DIR__.'/../../data/Composer/Psr4')
        );

        $classes = \iterator_to_array($iterator);

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
}
