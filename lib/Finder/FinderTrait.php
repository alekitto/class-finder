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
    private ?string $extends = null;

    /** @phpstan-var class-string|null */
    private ?string $annotation = null;

    /** @phpstan-var class-string|null */
    private ?string $attribute = null;

    /** @var string[] */
    private ?array $dirs = null;

    /** @var string[] */
    private ?array $namespaces = null;

    /** @var string[] */
    private ?array $paths = null;

    /** @var string[] */
    private ?array $notPaths = null;

    /** @var callable|null */
    private $callback = null;

    /**
     * {@inheritdoc}
     */
    public function implementationOf($interface): self
    {
        $this->implements = (array) $interface;

        return $this;
    }

    public function subclassOf(?string $superClass): self
    {
        $this->extends = $superClass;

        return $this;
    }

    public function annotatedBy(?string $annotationClass): self
    {
        $this->annotation = $annotationClass;

        return $this;
    }

    public function withAttribute(?string $attributeClass): self
    {
        $this->attribute = $attributeClass;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function in($dirs): self
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
     * {@inheritdoc}
     */
    public function inNamespace($namespaces): self
    {
        $this->namespaces = array_unique(array_merge($this->namespaces ?? [], (array) $namespaces));

        return $this;
    }

    public function filter(?callable $callback): self
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function path($pattern): self
    {
        $this->paths[] = $pattern;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function notPath($pattern): self
    {
        $this->notPaths[] = $pattern;

        return $this;
    }
}
