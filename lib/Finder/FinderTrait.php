<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Finder;

use Kcs\ClassFinder\FilterIterator\Reflection as Filters;
use Kcs\ClassFinder\PathNormalizer;

trait FinderTrait
{
    /**
     * @var string[]
     */
    private $implements = [];

    /**
     * @var string
     */
    private $extends = null;

    /**
     * @var string
     */
    private $annotation = null;

    /**
     * @var string[]
     */
    private $dirs = null;

    /**
     * @var string[]
     */
    private $namespaces = null;

    /**
     * @var callable
     */
    private $callback = null;

    /**
     * {@inheritdoc}
     */
    public function implementationOf($interface): FinderInterface
    {
        $this->implements = (array) $interface;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function subclassOf(?string $superClass): FinderInterface
    {
        $this->extends = $superClass;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function annotatedBy(?string $annotationClass): FinderInterface
    {
        $this->annotation = $annotationClass;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function in($dirs): FinderInterface
    {
        $resolvedDirs = [];

        foreach ((array) $dirs as $dir) {
            if (is_dir($dir)) {
                $resolvedDirs[] = $dir;
            } elseif ($glob = glob($dir, (defined('GLOB_BRACE') ? GLOB_BRACE : 0) | GLOB_ONLYDIR)) {
                $resolvedDirs = array_merge($resolvedDirs, $glob);
            } else {
                throw new \InvalidArgumentException('The "'.$dir.'" directory does not exist.');
            }
        }

        $resolvedDirs = array_map(PathNormalizer::class.'::resolvePath', $resolvedDirs);
        $this->dirs = array_unique(array_merge($this->dirs ?? [], $resolvedDirs));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function inNamespace($namespaces): FinderInterface
    {
        $this->namespaces = array_unique(array_merge($this->namespaces ?? [], (array) $namespaces));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(?callable $callback): FinderInterface
    {
        $this->callback = $callback;

        return $this;
    }

    private function applyFilters(\Iterator $iterator): \Iterator
    {
        if ($this->namespaces) {
            $iterator = new Filters\NamespaceFilterIterator($iterator, $this->namespaces);
        }

        if ($this->dirs) {
            $iterator = new Filters\DirectoryFilterIterator($iterator, $this->dirs);
        }

        if ($this->implements) {
            $iterator = new Filters\InterfaceImplementationFilterIterator($iterator, $this->implements);
        }

        if ($this->extends) {
            $iterator = new Filters\SuperClassFilterIterator($iterator, $this->extends);
        }

        if ($this->annotation) {
            $iterator = new Filters\AnnotationFilterIterator($iterator, $this->annotation);
        }

        if ($this->callback) {
            $iterator = new \CallbackFilterIterator($iterator, function ($unused, $key) {
                return (bool) ($this->callback)($key);
            });
        }

        return $iterator;
    }
}
