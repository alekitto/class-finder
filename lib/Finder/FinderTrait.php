<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Finder;

use InvalidArgumentException;
use Kcs\ClassFinder\PathNormalizer;

use function array_map;
use function array_merge;
use function array_push;
use function array_unique;
use function defined;
use function is_dir;
use function Safe\glob;

use const GLOB_BRACE;
use const GLOB_ONLYDIR;

trait FinderTrait
{
    /**
     * @var string[]
     * @phpstan-var class-string[]
     */
    private array $implements = [];

    /** @phpstan-var class-string|null */
    private string|null $extends = null;

    /** @phpstan-var class-string|null */
    private string|null $annotation = null;

    /** @phpstan-var class-string|null */
    private string|null $attribute = null;

    /** @var string[] */
    private array|null $dirs = null;

    /** @var string[] */
    private array|null $namespaces = null;

    /** @var string[] */
    private array|null $notNamespaces = null;

    /** @var string[] */
    private array|null $paths = null;

    /** @var string[] */
    private array|null $notPaths = null;

    /** @var callable(object, string):bool|null */
    private $filterCallback = null;

    /** @var callable(string):bool|null */
    private $pathFilterCallback = null;

    private bool $skipNonInstantiable = false;
    private bool $skipBogonClasses = false;

    /**
     * {@inheritDoc}
     */
    public function implementationOf($interface): static
    {
        $this->implements = (array) $interface;

        return $this;
    }

    public function subclassOf(string|null $superClass): static
    {
        $this->extends = $superClass;

        return $this;
    }

    public function annotatedBy(string|null $annotationClass): static
    {
        $this->annotation = $annotationClass;

        return $this;
    }

    public function withAttribute(string|null $attributeClass): static
    {
        $this->attribute = $attributeClass;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function in($dirs): static
    {
        $resolvedDirs = [];

        foreach ((array) $dirs as $dir) {
            if (is_dir($dir)) {
                $resolvedDirs[] = $dir;
            } else {
                $glob = glob($dir, (defined('GLOB_BRACE') ? GLOB_BRACE : 0) | GLOB_ONLYDIR);
                if (empty($glob)) {
                    throw new InvalidArgumentException('The "' . $dir . '" directory does not exist.');
                }

                array_push($resolvedDirs, ...$glob);
            }
        }

        $resolvedDirs = array_map(PathNormalizer::class . '::resolvePath', $resolvedDirs);
        $this->dirs = array_unique(array_merge($this->dirs ?? [], $resolvedDirs));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function inNamespace($namespaces): static
    {
        $this->namespaces = array_unique(array_merge($this->namespaces ?? [], (array) $namespaces));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function notInNamespace($namespaces): static
    {
        $this->notNamespaces = array_unique(array_merge($this->notNamespaces ?? [], (array) $namespaces));

        return $this;
    }

    public function filter(callable|null $callback): static
    {
        $this->filterCallback = $callback;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function path($pattern): static
    {
        $this->paths[] = $pattern;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function notPath($pattern): static
    {
        $this->notPaths[] = $pattern;

        return $this;
    }

    public function pathFilter(callable|null $callback): static
    {
        $this->pathFilterCallback = $callback;

        return $this;
    }

    public function skipNonInstantiable(bool $skip = true): static
    {
        $this->skipNonInstantiable = $skip;

        return $this;
    }

    public function skipBogonFiles(bool $skip = true): static
    {
        $this->skipBogonClasses = $skip;

        return $this;
    }
}
