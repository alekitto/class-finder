<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

trait PsrIteratorTrait
{
    protected function exists(string $className): bool
    {
        return class_exists($className, false) ||
            interface_exists($className, false) ||
            trait_exists($className, false);
    }

    private function search(): \Generator
    {
        foreach (glob($this->path.'/*') as $path) {
            if (is_dir($path)) {
                $files = iterator_to_array(new \RecursiveIteratorIterator(
                    new \RecursiveCallbackFilterIterator(
                        new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS),
                        function (\SplFileInfo $file) { return '.' !== $file->getBasename()[0]; }
                    ),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                ));
                uasort($files, function (\SplFileInfo $a, \SplFileInfo $b) {
                    return (string) $a <=> (string) $b;
                });

                foreach ($files as $path => $info) {
                    if ($info->isFile()) {
                        yield $path => $info;
                    }
                }
            } elseif (is_file($path)) {
                yield $path => new \SplFileInfo($path);
            }
        }
    }
}
