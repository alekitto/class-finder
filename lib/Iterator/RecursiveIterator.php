<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use Generator;
use Kcs\ClassFinder\PathNormalizer;
use ReflectionClass;

use function defined;
use function get_declared_classes;
use function get_declared_interfaces;
use function get_declared_traits;
use function in_array;
use function Safe\preg_match;

final class RecursiveIterator extends ClassIterator
{
    use RecursiveIteratorTrait;

    /** @var callable|null */
    private $callback = null;

    public function __construct(string $path, int $flags = 0, callable|null $callback = null)
    {
        $this->path = PathNormalizer::resolvePath($path);

        $this->callback = $callback;

        parent::__construct($flags);
    }

    /** @return Generator<class-string, ReflectionClass> */
    protected function getGenerator(): Generator
    {
        $includedFiles = [];
        $pattern = defined('HHVM_VERSION') ? '/\\.(php|hh)$/i' : '/\\.php$/i';

        foreach ($this->search() as $path => $info) {
            if (! preg_match($pattern, $path, $m) || ! $info->isReadable()) {
                continue;
            }

            if($this->callback && !call_user_func($this->callback, $path, $info)) {
                continue;
            }

            require_once $path;
            $includedFiles[] = $path;
        }

        foreach ($this->getDeclaredClasses() as $className) {
            $reflClass = new ReflectionClass($className);

            if (! in_array($reflClass->getFileName(), $includedFiles, true)) {
                continue;
            }

            yield $className => $reflClass;
        }
    }

    private function getDeclaredClasses(): Generator
    {
        yield from get_declared_classes();
        yield from get_declared_interfaces();
        yield from get_declared_traits();
    }
}
