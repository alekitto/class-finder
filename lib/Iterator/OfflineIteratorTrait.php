<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use InvalidArgumentException;
use Kcs\ClassFinder\FilterIterator\Reflection\PathFilterIterator;
use Kcs\ClassFinder\PathNormalizer;

use function array_map;
use function array_merge;
use function array_push;
use function array_unique;
use function defined;
use function is_dir;
use function Safe\glob;
use function Safe\preg_match;
use function str_starts_with;

use const GLOB_BRACE;
use const GLOB_ONLYDIR;

trait OfflineIteratorTrait
{
    /** @var string[] */
    private array|null $dirs = null;

    /** @var string[] */
    private array|null $paths = null;

    /** @var string[] */
    private array|null $notPaths = null;

    /**
     * Adds a search directory.
     *
     * @param string|string[] $dirs
     */
    public function in(string|array $dirs): self
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

    /** @param string[] $patterns */
    public function path(array $patterns): self
    {
        $this->paths = array_map(PathFilterIterator::class . '::toRegex', $patterns);

        return $this;
    }

    /** @param string[] $patterns */
    public function notPath(array $patterns): self
    {
        $this->notPaths = array_map(PathFilterIterator::class . '::toRegex', $patterns);

        return $this;
    }

    private function accept(string $path): bool
    {
        if ($this->pathCallback && ! ($this->pathCallback)($path)) {
            return false;
        }

        return $this->acceptDirs($path) &&
            $this->acceptPaths($path);
    }

    private function acceptPaths(string $path): bool
    {
        // should at least not match one rule to exclude
        if ($this->notPaths !== null) {
            foreach ($this->notPaths as $regex) {
                if (preg_match($regex, $path)) {
                    return false;
                }
            }
        }

        // should at least match one rule
        if ($this->paths !== null) {
            foreach ($this->paths as $regex) {
                if (preg_match($regex, $path)) {
                    return true;
                }
            }

            return false;
        }

        // If there is no match rules, the file is accepted
        return true;
    }

    private function acceptDirs(string $path): bool
    {
        if ($this->dirs === null) {
            return true;
        }

        foreach ($this->dirs as $dir) {
            if (str_starts_with($path, $dir)) {
                return true;
            }
        }

        return false;
    }
}
