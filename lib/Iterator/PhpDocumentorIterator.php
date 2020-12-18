<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use FilesystemIterator;
use Generator;
use InvalidArgumentException;
use Kcs\ClassFinder\FilterIterator\Reflection\PathFilterIterator;
use Kcs\ClassFinder\PathNormalizer;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\File\LocalFile;
use phpDocumentor\Reflection\Php\Class_;
use phpDocumentor\Reflection\Php\Factory;
use phpDocumentor\Reflection\Php\File;
use phpDocumentor\Reflection\Php\Interface_;
use phpDocumentor\Reflection\Php\NodesFactory;
use phpDocumentor\Reflection\Php\ProjectFactoryStrategies;
use phpDocumentor\Reflection\Php\Trait_;
use PhpParser\PrettyPrinter\Standard as PrettyPrinter;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function array_map;
use function array_merge;
use function array_push;
use function array_unique;
use function assert;
use function defined;
use function is_dir;
use function is_file;
use function iterator_to_array;
use function ltrim;
use function Safe\glob;
use function Safe\preg_match;
use function Safe\uasort;
use function strpos;

use const GLOB_BRACE;
use const GLOB_ONLYDIR;

final class PhpDocumentorIterator extends ClassIterator
{
    private const EXTENSION_PATTERN = '/\\.php$/i';

    private string $path;

    /** @var string[] */
    private ?array $dirs = null;

    /** @var string[] */
    private ?array $paths = null;

    /** @var string[] */
    private ?array $notPaths = null;
    private ProjectFactoryStrategies $strategies;

    public function __construct(string $path, int $flags = 0)
    {
        $this->path = PathNormalizer::resolvePath($path);
        $this->strategies = new ProjectFactoryStrategies([
            new Factory\Argument(new PrettyPrinter()),
            new Factory\Class_(),
            new Factory\Define(new PrettyPrinter()),
            new Factory\GlobalConstant(new PrettyPrinter()),
            new Factory\ClassConstant(new PrettyPrinter()),
            new Factory\DocBlock(DocBlockFactory::createInstance()),
            new Factory\File(NodesFactory::createInstance()),
            new Factory\Function_(),
            new Factory\Interface_(),
            new Factory\Method(),
            new Factory\Property(new PrettyPrinter()),
            new Factory\Trait_(),
        ]);

        parent::__construct($flags);
    }

    /**
     * Adds a search directory.
     *
     * @param string|string[] $dirs
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
     * @param string[] $patterns
     */
    public function path(array $patterns): self
    {
        $this->paths = array_map(PathFilterIterator::class . '::toRegex', $patterns);

        return $this;
    }

    /**
     * @param string[] $patterns
     */
    public function notPath(array $patterns): self
    {
        $this->notPaths = array_map(PathFilterIterator::class . '::toRegex', $patterns);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function isInstantiable($reflector): bool
    {
        return $reflector instanceof Class_ && ! $reflector->isAbstract();
    }

    protected function getGenerator(): Generator
    {
        foreach ($this->scan() as $path => $info) {
            if (! $this->accept($path)) {
                continue;
            }

            if (! preg_match(self::EXTENSION_PATTERN, $path, $m) || ! $info->isReadable()) {
                continue;
            }

            $factory = new Factory\File(NodesFactory::createInstance());
            $reflector = $factory->create(new LocalFile($path), $this->strategies);
            assert($reflector instanceof File);

            yield from $this->processClasses($reflector->getClasses());
            yield from $this->processClasses($reflector->getInterfaces());
            yield from $this->processClasses($reflector->getTraits());
        }
    }

    /**
     * Processes classes array.
     *
     * @param Class_[]|Interface_[]|Trait_[] $classes
     *
     * @return Generator<string, (Class_|Interface_|Trait_)>
     */
    private function processClasses(array $classes): Generator
    {
        foreach ($classes as $reflector) {
            yield ltrim((string) $reflector->getFqsen(), '\\') => $reflector;
        }
    }

    private function scan(): Generator
    {
        foreach (glob($this->path . '/*') as $path) {
            if (is_dir($path)) {
                $files = iterator_to_array(new RecursiveIteratorIterator(
                    new RecursiveCallbackFilterIterator(
                        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS),
                        static function (SplFileInfo $file) {
                            return $file->getBasename()[0] !== '.';
                        }
                    ),
                    RecursiveIteratorIterator::LEAVES_ONLY
                ));
                uasort($files, static function (SplFileInfo $a, SplFileInfo $b) {
                    return (string) $a <=> (string) $b;
                });

                foreach ($files as $filepath => $info) {
                    if (! $info->isFile()) {
                        continue;
                    }

                    yield $filepath => $info;
                }
            } elseif (is_file($path)) {
                yield $path => new SplFileInfo($path);
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
            if (strpos($path, $dir) === 0) {
                return true;
            }
        }

        return false;
    }
}
