<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Finder;

use Kcs\ClassFinder\FileFinder\FileFinderInterface;

trait RecursiveFinderTrait
{
    private FileFinderInterface $fileFinder;

    public function withFileFinder(FileFinderInterface $fileFinder): static
    {
        $this->fileFinder = $fileFinder;

        return $this;
    }
}
