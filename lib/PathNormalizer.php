<?php declare(strict_types=1);

namespace Kcs\ClassFinder;

final class PathNormalizer
{
    /**
     * Resolve a path.
     *
     * This is different from the realpath resolution as it
     * does not check the real existence of the dir/file
     * and does not resolve any eventual symlink.
     *
     * @param string $path
     *
     * @return string
     */
    public static function resolvePath(string $path): string
    {
        if (DIRECTORY_SEPARATOR !== '/') {
            $path = \str_replace('/', DIRECTORY_SEPARATOR, $path);
        }

        $newPath = [];
        foreach (\explode(DIRECTORY_SEPARATOR, $path) as $pathPart) {
            if ('.' === $pathPart) {
                continue;
            }

            if ('..' === $pathPart && \count($newPath) > 0) {
                \array_pop($newPath);
            } else {
                $newPath[] = $pathPart;
            }
        }

        return \implode(DIRECTORY_SEPARATOR, $newPath);
    }
}
