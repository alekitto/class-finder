<?php

declare(strict_types=1);

namespace Kcs\ClassFinder;

use function array_pop;
use function count;
use function explode;
use function implode;
use function str_replace;

use const DIRECTORY_SEPARATOR;

final class PathNormalizer
{
    /**
     * Normalizes path separator to '/'.
     */
    public static function normalize(string $path): string
    {
        return str_replace(DIRECTORY_SEPARATOR, '/', $path);
    }

    /**
     * Resolve a path.
     *
     * This is different from the realpath resolution as it
     * does not check the real existence of the dir/file
     * and does not resolve any eventual symlink.
     */
    public static function resolvePath(string $path): string
    {
        if (DIRECTORY_SEPARATOR !== '/') {
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        }

        $newPath = [];
        foreach (explode(DIRECTORY_SEPARATOR, $path) as $pathPart) {
            if ($pathPart === '.') {
                continue;
            }

            if ($pathPart === '..' && count($newPath) > 0) {
                array_pop($newPath);
            } else {
                $newPath[] = $pathPart;
            }
        }

        return implode(DIRECTORY_SEPARATOR, $newPath);
    }
}
