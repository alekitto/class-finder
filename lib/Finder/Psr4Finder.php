<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Finder;

use Kcs\ClassFinder\Iterator\Psr4Iterator;
use Kcs\ClassFinder\PathNormalizer;

/**
 * Finds classes respecting psr-4 standard.
 */
final class Psr4Finder implements FinderInterface
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
        if ('\\' !== substr($namespace, -1)) {
            $namespace .= '\\';
        }

        $path = PathNormalizer::resolvePath($path);
        if (DIRECTORY_SEPARATOR !== substr($path, -1)) {
            $path .= DIRECTORY_SEPARATOR;
        }

        $this->namespace = $namespace;
        $this->path = $path;
    }

    public function getIterator(): \Iterator
    {
        return $this->applyFilters(new Psr4Iterator($this->namespace, $this->path));
    }
}
