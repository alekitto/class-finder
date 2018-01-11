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
     * Checks whether the given class is instantiable.
     *
     * @param BaseReflector $reflector
     *
     * @return bool
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

            if (! preg_match(self::EXTENSION_PATTERN, $path, $m) || ! $info->isReadable()) {
                continue;
            }

            ob_start();
            $reflector = new FileReflector($path);
            $reflector->process();
            ob_end_clean();

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
            yield ltrim($reflector->getName(), '\\') => $reflector;
        }
    }

    private function scan(): \Generator
    {
        foreach (glob($this->path.'/*') as $path) {
            if (is_dir($path)) {
                $files = iterator_to_array(new \RecursiveIteratorIterator(
                    new \RecursiveCallbackFilterIterator(
                        new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS),
                        function (\SplFileInfo $file) { return '.' !== $file->getBasename()[0]; }
                    ),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                ));
                uasort($files, function (\SplFileInfo $a, \SplFileInfo $b) {
                    return (string) $a <=> (string) $b;
                });

                foreach ($files as $path => $info) {
                    if ($info->isFile()) {
                        yield $path => $info;
                    }
                }
            } elseif (is_file($path)) {
                yield $path => new \SplFileInfo($path);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function accept($path): bool
    {
        if (null === $this->dirs) {
            return true;
        }

        foreach ($this->dirs as $dir) {
            if (0 === strpos($path, $dir)) {
                return true;
            }
        }

        return false;
    }
}
