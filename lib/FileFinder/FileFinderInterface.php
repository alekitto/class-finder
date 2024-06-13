<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FileFinder;

use SplFileInfo;

/**
 * The FileFinderInterface is responsible to find files matching a given pattern.
 */
interface FileFinderInterface
{
    /**
     * Searches for the given pattern and return the list of the matching files/folders.
     *
     * @return iterable<string, SplFileInfo>
     */
    public function search(string $pattern): iterable;
}
