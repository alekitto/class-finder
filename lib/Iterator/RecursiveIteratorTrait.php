<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

trait RecursiveIteratorTrait
{
    /**
     * @var string
     */
    private $path;

    private function search(): \Generator
    {
        foreach (\glob($this->path.'/*') as $path) {
            if (\is_dir($path)) {
                $files = \iterator_to_array(new \RecursiveIteratorIterator(
                    new \RecursiveCallbackFilterIterator(
                        new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS),
                        static function (\SplFileInfo $file) { return '.' !== $file->getBasename()[0]; }
                    ),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                ));

                foreach ($files as $filepath => $info) {
                    if ($info->isFile()) {
                        yield $filepath => $info;
                    }
                }
            } elseif (\is_file($path)) {
                yield $path => new \SplFileInfo($path);
            }
        }
    }
}
