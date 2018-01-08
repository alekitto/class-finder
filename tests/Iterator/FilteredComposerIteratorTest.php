<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\Iterator;

use Composer\Autoload\ClassLoader;
use Kcs\ClassFinder\Fixtures\Psr0;
use Kcs\ClassFinder\Fixtures\Psr4;
use Kcs\ClassFinder\Iterator\FilteredComposerIterator;
use PHPUnit\Framework\TestCase;

class FilteredComposerIteratorTest extends TestCase
{
    public function testComposerIteratorShouldWork()
    {
        $loader = new ClassLoader();
        (\Closure::bind(function () use ($loader) {
            $loader->classMap = [
                __CLASS__ => __FILE__,
            ];

            $loader->prefixDirsPsr4 = [
                'Kcs\\ClassFinder\\Fixtures\\Psr4\\' => [
                    __DIR__.'/../..'.'/data/Composer/Psr4',
                ],
            ];

            $loader->prefixesPsr0 = [
                'K' => [
                    'Kcs\\ClassFinder\\Fixtures\\Psr0\\' => [
                        __DIR__.'/../..'.'/data/Composer/Psr0',
                    ],
                ],
            ];
        }, null, ClassLoader::class))();

        $iterator = new FilteredComposerIterator($loader, null, null);

        $this->assertEquals([
            __CLASS__ => __FILE__,
            Psr4\BarBar::class => realpath(__DIR__.'/../..'.'/data/Composer/Psr4').'/BarBar.php',
            Psr4\Foobar::class => realpath(__DIR__.'/../..'.'/data/Composer/Psr4').'/Foobar.php',
            Psr4\AbstractClass::class => realpath(__DIR__.'/../../data/Composer/Psr4').'/AbstractClass.php',
            Psr4\FooInterface::class => realpath(__DIR__.'/../../data/Composer/Psr4').'/FooInterface.php',
            Psr4\FooTrait::class => realpath(__DIR__.'/../../data/Composer/Psr4').'/FooTrait.php',
            Psr4\SubNs\FooBaz::class => realpath(__DIR__.'/../../data/Composer/Psr4/SubNs').'/FooBaz.php',
            Psr0\BarBar::class => realpath(__DIR__.'/../..'.'/data/Composer/Psr0/Kcs/ClassFinder/Fixtures/Psr0').'/BarBar.php',
            Psr0\Foobar::class => realpath(__DIR__.'/../..'.'/data/Composer/Psr0/Kcs/ClassFinder/Fixtures/Psr0').'/Foobar.php',
            Psr0\SubNs\FooBaz::class => realpath(__DIR__.'/../..'.'/data/Composer/Psr0/Kcs/ClassFinder/Fixtures/Psr0/SubNs').'/FooBaz.php',
        ], iterator_to_array($iterator));
    }
}
