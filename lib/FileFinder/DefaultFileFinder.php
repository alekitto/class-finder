<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FileFinder;

use ErrorException;
use FilesystemIterator;
use Kcs\ClassFinder\PathNormalizer;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function error_get_last;
use function glob;
use function is_dir;
use function is_file;

use const E_ERROR;

final class DefaultFileFinder implements FileFinderInterface
{
    /** @inheritDoc */
    public function search(string $pattern): iterable
    {
        $matches = glob($pattern);
        if ($matches === false) {
            $error = error_get_last();

            throw new ErrorException($error['message'] ?? 'An error occurred while searching for files', 0, $error['type'] ?? E_ERROR);
        }

        foreach ($matches as $path) {
            if (is_dir($path)) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveCallbackFilterIterator(
                        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS),
                        static fn (SplFileInfo $file): bool => $file->getBasename()[0] !== '.',
                    ),
                    RecursiveIteratorIterator::LEAVES_ONLY,
                );

                foreach ($files as $filepath => $info) {
                    if (! $info->isFile()) {
                        continue;
                    }

                    yield PathNormalizer::resolvePath($filepath) => $info;
                }
            } elseif (is_file($path)) {
                yield PathNormalizer::resolvePath($path) => new SplFileInfo($path);
            }
        }
    }
}
