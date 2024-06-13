<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use Kcs\ClassFinder\FileFinder\DefaultFileFinder;
use Kcs\ClassFinder\FileFinder\FileFinderInterface;

trait RecursiveIteratorTrait
{
    private string $path;
    private FileFinderInterface $fileFinder;

    public function setFileFinder(FileFinderInterface $fileFinder): void
    {
        $this->fileFinder = $fileFinder;
    }

    /** @inheritDoc */
    private function search(): iterable
    {
        yield from ($this->fileFinder ?? new DefaultFileFinder())
            ->search($this->path . '/*');
    }
}
