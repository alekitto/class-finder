<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Finder;

use Kcs\ClassFinder\Iterator\Psr0Iterator;
use Kcs\ClassFinder\PathNormalizer;

/**
 * Finds classes respecting psr-4 standard.
 */
final class Psr0Finder implements FinderInterface
{
    use ReflectionFilterTrait;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $path;

    public function __construct(string $namespace, string $path)
    {
        if ('\\' !== \substr($namespace, -1)) {
            $namespace .= '\\';
        }

        $path = PathNormalizer::resolvePath($path);
        if (DIRECTORY_SEPARATOR !== \substr($path, -1)) {
            $path .= DIRECTORY_SEPARATOR;
        }

        $this->namespace = $namespace;
        $this->path = PathNormalizer::resolvePath($path);
    }

    public function getIterator(): \Iterator
    {
        return $this->applyFilters(new Psr0Iterator($this->namespace, $this->path));
    }
}
