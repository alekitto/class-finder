<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use Kcs\ClassFinder\FileFinder\DefaultFileFinder;
use Kcs\ClassFinder\FileFinder\FileFinderInterface;
use SplFileInfo;

trait RecursiveIteratorTrait
{
    private string $path;
    private FileFinderInterface $fileFinder;

    public function setFileFinder(FileFinderInterface $fileFinder): void
    {
        $this->fileFinder = $fileFinder;
    }

    /** @return iterable<string, SplFileInfo> */
    private function search(): iterable
    {
        yield from ($this->fileFinder ?? new DefaultFileFinder())
            ->search($this->path . '/*');
    }
}
