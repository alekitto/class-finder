<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use Kcs\ClassFinder\PathNormalizer;
use phpDocumentor\Reflection\BaseReflector;
use phpDocumentor\Reflection\ClassReflector;
use phpDocumentor\Reflection\FileReflector;

final class PhpDocumentorIterator extends ClassIterator
{
    private const EXTENSION_PATTERN = '/\\.php$/i';

    /**
     * @var string
     */
    private $path;

    /**
     * @var string[]
     */
    private $dirs = null;

    /**
     * @var string[]
     */
    private $paths = null;

    /**
     * @var string[]
     */
    private $notPaths = null;

    public function __construct(string $path, int $flags = 0)
    {
        $this->path = PathNormalizer::resolvePath($path);

        parent::__construct($flags);
    }

    /**
     * Adds a search directory.
     *
     * @param string|string[] $dirs
     *
     * @return $this
     */
    public function in($dirs): self
    {
        $resolvedDirs = [];

        foreach ((array) $dirs as $dir) {
            if (\is_dir($dir)) {
                $resolvedDirs[] = $dir;
            } elseif ($glob = \glob($dir, (\defined('GLOB_BRACE') ? GLOB_BRACE : 0) | GLOB_ONLYDIR)) {
                $resolvedDirs = \array_merge($resolvedDirs, $glob);
            } else {
                throw new \InvalidArgumentException('The "'.$dir.'" directory does not exist.');
            }
        }

        $resolvedDirs = \array_map(PathNormalizer::class.'::resolvePath', $resolvedDirs);
        $this->dirs = \array_unique(\array_merge($this->dirs ?? [], $resolvedDirs));

        return $this;
    }

    public function path(array $patterns): self
    {
        $this->paths = \array_map('Kcs\ClassFinder\FilterIterator\Reflection\PathFilterIterator::toRegex', $patterns);

        return $this;
    }

    public function notPath($patterns): self
    {
        $this->notPaths = \array_map('Kcs\ClassFinder\FilterIterator\Reflection\PathFilterIterator::toRegex', $patterns);

        return $this;
    }

    /**
     * Checks whether the given class is instantiable.
     *
     * @param BaseReflector $reflector
     */
    protected function isInstantiable($reflector): bool
    {
        return $reflector instanceof ClassReflector && ! $reflector->isAbstract();
    }

    /**
     * {@inheritdoc}
     */
    protected function getGenerator(): \Generator
    {
        foreach ($this->scan() as $path => $info) {
            if (! $this->accept($path)) {
                continue;
            }

            if (! \preg_match(self::EXTENSION_PATTERN, $path, $m) || ! $info->isReadable()) {
                continue;
            }

            \ob_start();
            $reflector = new FileReflector($path);
            $reflector->process();
            \ob_end_clean();

            yield from $this->processClasses($reflector->getClasses());
            yield from $this->processClasses($reflector->getInterfaces());
            yield from $this->processClasses($reflector->getTraits());
        }
    }

    /**
     * Processes classes array.
     *
     * @param BaseReflector[] $classes
     *
     * @return \Generator|BaseReflector[]
     */
    private function processClasses(array $classes): \Generator
    {
        foreach ($classes as $reflector) {
            yield \ltrim($reflector->getName(), '\\') => $reflector;
        }
    }

    private function scan(): \Generator
    {
        foreach (\glob($this->path.'/*') as $path) {
            if (\is_dir($path)) {
                $files = \iterator_to_array(new \RecursiveIteratorIterator(
                    new \RecursiveCallbackFilterIterator(
                        new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS),
                        static function (\SplFileInfo $file) { return '.' !== $file->getBasename()[0]; }
                    ),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                ));
                \uasort($files, static function (\SplFileInfo $a, \SplFileInfo $b) {
                    return (string) $a <=> (string) $b;
                });

                foreach ($files as $filepath => $info) {
                    if ($info->isFile()) {
                        yield $filepath => $info;
                    }
                }
            } elseif (\is_file($path)) {
                yield $path => new \SplFileInfo($path);
            }
        }
    }

    private function accept(string $path): bool
    {
        return $this->acceptDirs($path) &&
            $this->acceptPaths($path);
    }

    private function acceptPaths(string $path): bool
    {
        // should at least not match one rule to exclude
        if (null !== $this->notPaths) {
            foreach ($this->notPaths as $regex) {
                if (\preg_match($regex, $path)) {
                    return false;
                }
            }
        }

        // should at least match one rule
        if (null !== $this->paths) {
            foreach ($this->paths as $regex) {
                if (\preg_match($regex, $path)) {
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
        if (null === $this->dirs) {
            return true;
        }

        foreach ($this->dirs as $dir) {
            if (0 === \strpos($path, $dir)) {
                return true;
            }
        }

        return false;
    }
}
