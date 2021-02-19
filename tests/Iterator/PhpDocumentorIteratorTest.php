<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\Iterator;

use Kcs\ClassFinder\Fixtures\Psr4;
use Kcs\ClassFinder\Iterator\PhpDocumentorIterator;
use phpDocumentor\Reflection\Php\Class_;
use phpDocumentor\Reflection\Php\Interface_;
use phpDocumentor\Reflection\Php\Trait_;
use PHPUnit\Framework\TestCase;

use function iterator_to_array;
use function realpath;

class PhpDocumentorIteratorTest extends TestCase
{
    public function testIteratorShouldWork()
    {
        $iterator = new PhpDocumentorIterator(
            realpath(__DIR__ . '/../../data/Composer/Psr4')
        );

        $classes = iterator_to_array($iterator);

        self::assertArrayHasKey(Psr4\BarBar::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr4\BarBar::class]);
        self::assertArrayHasKey(Psr4\Foobar::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr4\Foobar::class]);
        self::assertArrayHasKey(Psr4\AbstractClass::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr4\AbstractClass::class]);
        self::assertArrayHasKey(Psr4\SubNs\FooBaz::class, $classes);
        self::assertInstanceOf(Class_::class, $classes[Psr4\SubNs\FooBaz::class]);
        self::assertArrayHasKey(Psr4\FooInterface::class, $classes);
        self::assertInstanceOf(Interface_::class, $classes[Psr4\FooInterface::class]);
        self::assertArrayHasKey(Psr4\FooTrait::class, $classes);
        self::assertInstanceOf(Trait_::class, $classes[Psr4\FooTrait::class]);
    }
}
