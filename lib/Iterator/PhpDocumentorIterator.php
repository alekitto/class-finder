<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use Closure;
use Generator;
use InvalidArgumentException;
use Kcs\ClassFinder\FilterIterator\Reflection\PathFilterIterator;
use Kcs\ClassFinder\PathNormalizer;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use phpDocumentor\Reflection\File\LocalFile;
use phpDocumentor\Reflection\Php\Class_;
use phpDocumentor\Reflection\Php\Factory;
use phpDocumentor\Reflection\Php\File;
use phpDocumentor\Reflection\Php\Interface_;
use phpDocumentor\Reflection\Php\NodesFactory;
use phpDocumentor\Reflection\Php\Project;
use phpDocumentor\Reflection\Php\ProjectFactoryStrategies;
use phpDocumentor\Reflection\Php\Trait_;
use PhpParser\PrettyPrinter\Standard as PrettyPrinter;

use function array_map;
use function array_merge;
use function array_push;
use function array_unique;
use function assert;
use function class_exists;
use function defined;
use function is_dir;
use function ltrim;
use function Safe\glob;
use function Safe\preg_match;
use function str_starts_with;

use const GLOB_BRACE;
use const GLOB_ONLYDIR;

final class PhpDocumentorIterator extends ClassIterator
{
    use RecursiveIteratorTrait;

    private const EXTENSION_PATTERN = '/\\.php$/i';

    /** @var string[] */
    private array|null $dirs = null;

    /** @var string[] */
    private array|null $paths = null;

    /** @var string[] */
    private array|null $notPaths = null;
    private ProjectFactoryStrategies $strategies;
    private DocBlockFactory|DocBlockFactoryInterface $docBlockFactory;

    /** @param string[]|null $excludeNamespaces */
    public function __construct(string $path, int $flags = 0, array|null $excludeNamespaces = null, Closure|null $pathCallback = null)
    {
        $this->path = PathNormalizer::resolvePath($path);
        $this->docBlockFactory = DocBlockFactory::createInstance();
        if (class_exists(Factory\DocBlock::class)) {
            // phpdoc/reflection 4.x
            $this->strategies = new ProjectFactoryStrategies([ // @phpstan-ignore-line
                new Factory\Argument(new PrettyPrinter()), // @phpstan-ignore-line
                new Factory\Class_(), // @phpstan-ignore-line
                new Factory\Define(new PrettyPrinter()), // @phpstan-ignore-line
                new Factory\GlobalConstant(new PrettyPrinter()), // @phpstan-ignore-line
                new Factory\ClassConstant(new PrettyPrinter()), // @phpstan-ignore-line
                new Factory\DocBlock($this->docBlockFactory),
                new Factory\File(NodesFactory::createInstance()), // @phpstan-ignore-line
                new Factory\Function_(), // @phpstan-ignore-line
                new Factory\Interface_(), // @phpstan-ignore-line
                new Factory\Method(), // @phpstan-ignore-line
                new Factory\Property(new PrettyPrinter()), // @phpstan-ignore-line
                new Factory\Trait_(), // @phpstan-ignore-line
            ]);
        } elseif (class_exists(Factory\Argument::class)) {
            $this->strategies = new ProjectFactoryStrategies([ // @phpstan-ignore-line
                new Factory\Argument(new PrettyPrinter()),
                new Factory\Class_($this->docBlockFactory),
                new Factory\Define($this->docBlockFactory, new PrettyPrinter()),
                new Factory\GlobalConstant($this->docBlockFactory, new PrettyPrinter()),
                new Factory\ClassConstant($this->docBlockFactory, new PrettyPrinter()),
                new Factory\File($this->docBlockFactory, NodesFactory::createInstance()),
                new Factory\Function_($this->docBlockFactory),
                new Factory\Namespace_(),
                new Factory\Interface_($this->docBlockFactory),
                new Factory\Method($this->docBlockFactory),
                new Factory\Property($this->docBlockFactory, new PrettyPrinter()),
                new Factory\Trait_($this->docBlockFactory),
            ]);

            $this->strategies->addStrategy(new Factory\Noop(), -1000);
        } else {
            $attributeReducer = new Factory\Reducer\Attribute();
            $parameterReducer = new Factory\Reducer\Parameter(new PrettyPrinter());
            $methodStrategy = new Factory\Method($this->docBlockFactory, [$attributeReducer, $parameterReducer]);

            $this->strategies = new ProjectFactoryStrategies([
                new Factory\Namespace_(),
                new Factory\Class_($this->docBlockFactory, [$attributeReducer]),
                new Factory\Enum_($this->docBlockFactory, [$attributeReducer]),
                new Factory\EnumCase($this->docBlockFactory, new PrettyPrinter()),
                new Factory\Define($this->docBlockFactory, new PrettyPrinter()),
                new Factory\GlobalConstant($this->docBlockFactory, new PrettyPrinter()),
                new Factory\ClassConstant($this->docBlockFactory, new PrettyPrinter()),
                new Factory\File($this->docBlockFactory, NodesFactory::createInstance()),
                new Factory\Function_($this->docBlockFactory, [$attributeReducer, $parameterReducer]),
                new Factory\Interface_($this->docBlockFactory),
                $methodStrategy,
                new Factory\Property($this->docBlockFactory, new PrettyPrinter()),
                new Factory\Trait_($this->docBlockFactory),

                new Factory\IfStatement(),
                new Factory\TraitUse(),
            ]);

            $this->strategies->addStrategy(new Factory\ConstructorPromotion($methodStrategy, $this->docBlockFactory, new PrettyPrinter()), -1000);
            $this->strategies->addStrategy(new Factory\Noop(), -1000);
        }

        parent::__construct($flags, $excludeNamespaces, $pathCallback);
    }

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

    protected function isInstantiable(mixed $reflector): bool
    {
        return $reflector instanceof Class_ && ! $reflector->isAbstract();
    }

    protected function getGenerator(): Generator
    {
        foreach ($this->search() as $path => $info) {
            if (! $this->accept(PathNormalizer::resolvePath($path))) {
                continue;
            }

            if (! preg_match(self::EXTENSION_PATTERN, $path, $m) || ! $info->isReadable()) {
                continue;
            }

            if (class_exists(Factory\DocBlock::class)) {
                // phpdoc/reflection 4.x
                $factory = new Factory\File(NodesFactory::createInstance()); // @phpstan-ignore-line
                $reflector = $factory->create(new LocalFile($path), $this->strategies); // @phpstan-ignore-line
            } else {
                $project = new Project('project');
                $contextStack = new Factory\ContextStack($project);
                $factory = new Factory\File($this->docBlockFactory, NodesFactory::createInstance());
                $factory->create($contextStack, new LocalFile($path), $this->strategies);
                $reflector = $project->getFiles()[$path];
            }

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
            $className = (string) $reflector->getFqsen();
            if (! $this->validNamespace($className)) {
                continue;
            }

            yield ltrim($className, '\\') => $reflector;
        }
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
