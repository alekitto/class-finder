<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use Kcs\ClassFinder\PathNormalizer;

final class RecursiveIterator extends ClassIterator
{
    use PsrIteratorTrait;
    use RecursiveIteratorTrait;

    public function __construct(string $path, int $flags = 0)
    {
        $this->path = PathNormalizer::resolvePath($path);

        parent::__construct($flags);
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
