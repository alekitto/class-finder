<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use FilesystemIterator;
use Generator;
use Kcs\ClassFinder\PathNormalizer;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function is_dir;
use function is_file;
use function Safe\glob;

trait RecursiveIteratorTrait
{
    private string $path;

    private function search(): Generator
    {
        foreach (glob($this->path . '/*') as $path) {
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
