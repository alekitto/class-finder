<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\unit\FileFinder;

use Kcs\ClassFinder\FileFinder\DefaultFileFinder;
use PHPUnit\Framework\TestCase;

class DefaultFileFinderTest extends TestCase
{
    public function testSearchInFolder(): void
    {
        $finder = new DefaultFileFinder();
        $itr = $finder->search(__DIR__ . '/../../../data/Recursive/*.php');

        $files = array_keys(iterator_to_array($itr));
        sort($files);

        self::assertEquals([
            realpath(__DIR__ . '/../../../data/Recursive/Bar.php'),
            realpath(__DIR__ . '/../../../data/Recursive/Foo.php'),
            realpath(__DIR__ . '/../../../data/Recursive/bootstrap.php'),
            realpath(__DIR__ . '/../../../data/Recursive/class-foo-bar.php'),
        ], $files);
    }

    public function testSearchReturnsEmpty(): void
    {
        $finder = new DefaultFileFinder();
        $itr = $finder->search(__DIR__ . '/../../../data/Recursive/*.empty');

        self::assertEquals([], iterator_to_array($itr));
    }
}
