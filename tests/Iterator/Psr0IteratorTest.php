<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\Iterator;

use Kcs\ClassFinder\Fixtures\Psr0;
use Kcs\ClassFinder\Iterator\Psr0Iterator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function iterator_to_array;
use function realpath;

class Psr0IteratorTest extends TestCase
{
    public function testIteratorShouldWork()
    {
        $iterator = new Psr0Iterator(
            'Kcs\\ClassFinder\\Fixtures\\Psr0\\',
            realpath(__DIR__ . '/../../data/Composer/Psr0')
        );

        self::assertEquals([
            Psr0\BarBar::class => new ReflectionClass(Psr0\BarBar::class),
            Psr0\Foobar::class => new ReflectionClass(Psr0\Foobar::class),
            Psr0\SubNs\FooBaz::class => new ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], iterator_to_array($iterator));
    }
}
