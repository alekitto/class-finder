<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use Closure;
use Generator;
use Kcs\ClassFinder\PathNormalizer;
use Kcs\ClassFinder\Util\Offline\Metadata;
use Kcs\ClassFinder\Util\PhpDocumentor\MetadataRegistry;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use phpDocumentor\Reflection\File\LocalFile;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Metadata\Metadata as PhpDocMetadata;
use phpDocumentor\Reflection\Php\Class_;
use phpDocumentor\Reflection\Php\Enum_;
use phpDocumentor\Reflection\Php\Factory;
use phpDocumentor\Reflection\Php\File;
use phpDocumentor\Reflection\Php\Interface_;
use phpDocumentor\Reflection\Php\NodesFactory;
use phpDocumentor\Reflection\Php\Project;
use phpDocumentor\Reflection\Php\ProjectFactoryStrategies;
use phpDocumentor\Reflection\Php\Trait_;
use PhpParser\PrettyPrinter\Standard as PrettyPrinter;
use Throwable;

use function array_map;
use function array_push;
use function array_values;
use function assert;
use function class_exists;
use function is_string;
use function ltrim;
use function method_exists;
use function Safe\preg_match;

final class PhpDocumentorIterator extends ClassIterator
{
    use OfflineIteratorTrait;
    use RecursiveIteratorTrait;

    private const EXTENSION_PATTERN = '/\\.php$/i';

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

    protected function isInstantiable(mixed $reflector): bool
    {
        return $reflector instanceof Class_ && ! $reflector->isAbstract();
    }

    protected function getGenerator(): Generator
    {
        if (class_exists(Factory\DocBlock::class)) {
            // phpdoc/reflection 4.x
            $addMetadata = static function (object $target, PhpDocMetadata $metadata): void {
                MetadataRegistry::getInstance()->addMetadata($target, $metadata);
            };
        } else {
            $addMetadata = static function (object $target, PhpDocMetadata $metadata): void {
                assert(method_exists($target, 'addMetadata'));
                $target->addMetadata($metadata);
            };
        }

        $symbols = $files = [];
        foreach ($this->search() as $path => $info) {
            if (! preg_match(self::EXTENSION_PATTERN, $path, $m) || ! $info->isReadable()) {
                continue;
            }

            try {
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
            } catch (Throwable) { /** @phpstan-ignore-line */
                continue;
            }

            assert($reflector instanceof File);
            $enums = method_exists($reflector, 'getEnums') ? $reflector->getEnums() : [];
            $fileSymbols = array_map(
                static function (object $class) use (&$symbols): object {
                    $symbols[(string) $class->getFqsen()] = $class;

                    return $class;
                },
                [...$reflector->getClasses(), ...$reflector->getInterfaces(), ...$reflector->getTraits(), ...$enums],
            );

            if (! $this->accept(PathNormalizer::resolvePath($path))) {
                continue;
            }

            $files[$path] = $fileSymbols;
        }

        foreach ($files as $path => $fileSymbols) {
            assert(is_string($path));

            foreach ($fileSymbols as $fileSymbol) {
                if ($fileSymbol instanceof Class_) {
                    $parents = [];
                    $interfaces = [];
                    $this->processInterfaces($interfaces, $fileSymbol->getInterfaces(), $symbols);

                    $parent = $fileSymbol;
                    while (($parentName = $parent->getParent())) {
                        $parent = $symbols[(string) $parentName] ?? null;
                        if ($parent === null) {
                            break; // We don't have information on the parent class.
                        }

                        assert($parent instanceof Class_);
                        $parents[] = $parent;
                        $this->processInterfaces($interfaces, $parent->getInterfaces(), $symbols);
                    }

                    $addMetadata($fileSymbol, new Metadata($path, [...$parents, ...array_values($interfaces)]));
                } elseif ($fileSymbol instanceof Interface_) {
                    $interfaces = [];
                    $this->processInterfaces($interfaces, $fileSymbol->getParents(), $symbols);
                    $addMetadata($fileSymbol, new Metadata($path, array_values($interfaces)));
                } elseif ($fileSymbol instanceof Trait_ || $fileSymbol instanceof Enum_) {
                    $addMetadata($fileSymbol, new Metadata($path, []));
                }
            }

            yield from $this->processClasses($fileSymbols);
        }
    }

    /**
     * Processes classes array.
     *
     * @param array<Class_|Interface_|Trait_|Enum_> $classes
     *
     * @return Generator<string, Class_|Interface_|Trait_|Enum_>
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

    /**
     * @param Interface_[] $interfaces
     * @param Fqsen[] $parents
     * @param array<string, object> $symbols
     */
    private function processInterfaces(array &$interfaces, array $parents, array &$symbols): void
    {
        while ($parents) {
            $currentLevel = [];
            foreach ($parents as $parentName) {
                $p = $symbols[(string) $parentName] ?? null;
                if (! $p instanceof Interface_) {
                    continue;
                }

                $interfaces[(string) $parentName] = $p;
                array_push($currentLevel, ...array_values($p->getParents()));
            }

            $parents = $currentLevel;
        }
    }
}
