<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\Iterator;

use Kcs\ClassFinder\Fixtures\Psr4;
use Kcs\ClassFinder\Iterator\Psr4Iterator;
use PHPUnit\Framework\TestCase;

class Psr4IteratorTest extends TestCase
{
    public function testIteratorShouldWork()
    {
        $iterator = new Psr4Iterator(
            'Kcs\\ClassFinder\\Fixtures\\Psr4\\',
            realpath(__DIR__.'/../../data/Composer/Psr4')
        );

        $this->assertEquals([
            Psr4\BarBar::class => realpath(__DIR__.'/../../data/Composer/Psr4').'/BarBar.php',
            Psr4\Foobar::class => realpath(__DIR__.'/../../data/Composer/Psr4').'/Foobar.php',
            Psr4\AbstractClass::class => realpath(__DIR__.'/../../data/Composer/Psr4').'/AbstractClass.php',
            Psr4\FooInterface::class => realpath(__DIR__.'/../../data/Composer/Psr4').'/FooInterface.php',
            Psr4\FooTrait::class => realpath(__DIR__.'/../../data/Composer/Psr4').'/FooTrait.php',
            Psr4\SubNs\FooBaz::class => realpath(__DIR__.'/../../data/Composer/Psr4/SubNs').'/FooBaz.php',
        ], iterator_to_array($iterator));
    }
}
