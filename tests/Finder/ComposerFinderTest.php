<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\Finder;

use Kcs\ClassFinder\Finder\ComposerFinder;
use Kcs\ClassFinder\Fixtures\Psr0;
use Kcs\ClassFinder\Fixtures\Psr4;
use PHPUnit\Framework\TestCase;

class ComposerFinderTest extends TestCase
{
    public function testFinderShouldBeIterable()
    {
        $finder = new ComposerFinder();
        $this->assertInstanceOf(\Traversable::class, $finder);
    }

    public function testFinderShouldFilterByNamespace()
    {
        $finder = new ComposerFinder();
        $finder->inNamespace(['Kcs\ClassFinder\Fixtures\Psr4']);

        $this->assertEquals([
            Psr4\BarBar::class => realpath(__DIR__.'/../../data/Composer/Psr4') . '/BarBar.php',
            Psr4\Foobar::class => realpath(__DIR__.'/../../data/Composer/Psr4') . '/Foobar.php',
            Psr4\AbstractClass::class => realpath(__DIR__.'/../../data/Composer/Psr4') . '/AbstractClass.php',
            Psr4\FooInterface::class => realpath(__DIR__.'/../../data/Composer/Psr4') . '/FooInterface.php',
            Psr4\FooTrait::class => realpath(__DIR__.'/../../data/Composer/Psr4') . '/FooTrait.php',
            Psr4\SubNs\FooBaz::class => realpath(__DIR__.'/../../data/Composer/Psr4/SubNs') . '/FooBaz.php',
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByDirectory()
    {
        $finder = new ComposerFinder();
        $finder->in([__DIR__ . '/../../data/Composer/Psr0']);

        $this->assertEquals([
            Psr0\BarBar::class => realpath(__DIR__.'/../../data/Composer/Psr0/Kcs/ClassFinder/Fixtures/Psr0') . '/BarBar.php',
            Psr0\Foobar::class => realpath(__DIR__.'/../../data/Composer/Psr0/Kcs/ClassFinder/Fixtures/Psr0') . '/Foobar.php',
            Psr0\SubNs\FooBaz::class => realpath(__DIR__.'/../../data/Composer/Psr0/Kcs/ClassFinder/Fixtures/Psr0/SubNs') . '/FooBaz.php',
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterByInterfaceImplementation()
    {
        $finder = new ComposerFinder();
        $finder->in([__DIR__.'/../../data']);
        $finder->implementationOf(Psr4\FooInterface::class);

        $this->assertEquals([
            Psr4\BarBar::class => realpath(__DIR__.'/../../data/Composer/Psr4') . '/BarBar.php',
        ], iterator_to_array($finder));
    }

    public function testFinderShouldFilterBySuperClass()
    {
        $finder = new ComposerFinder();
        $finder->in([__DIR__.'/../../data']);
        $finder->subclassOf(Psr4\AbstractClass::class);

        $this->assertEquals([
            Psr4\Foobar::class => realpath(__DIR__.'/../../data/Composer/Psr4') . '/Foobar.php',
        ], iterator_to_array($finder));
    }
}
