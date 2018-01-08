<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\Iterator;

use Kcs\ClassFinder\Fixtures\Psr0;
use Kcs\ClassFinder\Iterator\Psr0Iterator;
use PHPUnit\Framework\TestCase;

class Psr0IteratorTest extends TestCase
{
    public function testIteratorShouldWork()
    {
        $iterator = new Psr0Iterator(
            'Kcs\\ClassFinder\\Fixtures\\Psr0\\',
            realpath(__DIR__.'/../../data/Composer/Psr0')
        );

        $this->assertEquals([
            Psr0\BarBar::class => realpath(__DIR__.'/../../data/Composer/Psr0/Kcs/ClassFinder/Fixtures/Psr0').'/BarBar.php',
            Psr0\Foobar::class => realpath(__DIR__.'/../../data/Composer/Psr0/Kcs/ClassFinder/Fixtures/Psr0').'/Foobar.php',
            Psr0\SubNs\FooBaz::class => realpath(__DIR__.'/../../data/Composer/Psr0/Kcs/ClassFinder/Fixtures/Psr0/SubNs').'/FooBaz.php',
        ], iterator_to_array($iterator));
    }
}
