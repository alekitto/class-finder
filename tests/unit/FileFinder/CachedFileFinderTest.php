<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\unit\FileFinder;

use Kcs\ClassFinder\FileFinder\CachedFileFinder;
use Kcs\ClassFinder\FileFinder\FileFinderInterface;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class CachedFileFinderTest extends TestCase
{
    public function testSearchInFolder(): void
    {
        $inner = $this->createMock(FileFinderInterface::class);
        $inner->expects(self::once())
            ->method('search')
            ->willReturn(['/test_file.php' => new SplFileInfo(__FILE__)]);

        $finder = new CachedFileFinder(
            $inner,
            $cache = new ArrayAdapter(),
        );

        $itr = iterator_to_array($finder->search('/*.php'));
        iterator_to_array($finder->search('/*.php'));
        iterator_to_array($finder->search('/*.php'));

        $values = $cache->getValues();
        self::assertCount(1, $values);
        self::assertEquals(['/test_file.php'], array_keys($itr));
    }
}
