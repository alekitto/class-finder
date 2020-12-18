<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Finder;

use Iterator;
use Kcs\ClassFinder\Iterator\Psr0Iterator;
use Kcs\ClassFinder\PathNormalizer;
use Reflector;

use function Safe\substr;

use const DIRECTORY_SEPARATOR;

/**
 * Finds classes respecting psr-4 standard.
 */
final class Psr0Finder implements FinderInterface
{
    use ReflectionFilterTrait;

    private string $namespace;
    private string $path;

    public function __construct(string $namespace, string $path)
    {
        if (substr($namespace, -1) !== '\\') {
            $namespace .= '\\';
        }

        $path = PathNormalizer::resolvePath($path);
        if (substr($path, -1) !== DIRECTORY_SEPARATOR) {
            $path .= DIRECTORY_SEPARATOR;
        }

        $this->namespace = $namespace;
        $this->path = PathNormalizer::resolvePath($path);
    }

    /**
     * @return Iterator<Reflector>
     */
    public function getIterator(): Iterator
    {
        return $this->applyFilters(new Psr0Iterator($this->namespace, $this->path));
    }
}
