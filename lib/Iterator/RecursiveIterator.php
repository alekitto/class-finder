<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use Kcs\ClassFinder\PathNormalizer;

final class RecursiveIterator extends ClassIterator
{
    use PsrIteratorTrait;

    /**
     * @var string
     */
    private $path;

    public function __construct(string $path, int $flags = 0)
    {
        $this->path = PathNormalizer::resolvePath($path);

        parent::__construct($flags);
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

    protected function getGenerator(): \Generator
    {
        $included_files = [];
        $pattern = defined('HHVM_VERSION') ? '/\\.(php|hh)$/i' : '/\\.php$/i';

        foreach ($this->search() as $path => $info) {
            if (! preg_match($pattern, $path, $m) || ! $info->isReadable()) {
                continue;
            }

            require_once $path;
            $included_files[] = $path;
        }

        foreach ($this->getDeclaredClasses() as $className) {
            $reflClass = new \ReflectionClass($className);

            if (in_array($reflClass->getFileName(), $included_files)) {
                yield $className => $reflClass;
            }
        }
    }

    private function getDeclaredClasses(): \Generator
    {
        yield from get_declared_classes();
        yield from get_declared_interfaces();
        yield from get_declared_traits();
    }
}
