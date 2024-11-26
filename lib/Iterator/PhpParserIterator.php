<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use Closure;
use Generator;
use Kcs\ClassFinder\PathNormalizer;
use Kcs\ClassFinder\Util\Offline\Metadata;
use Kcs\ClassFinder\Util\PhpParser\AnnotationParser;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\ParserFactory;

use function array_map;
use function array_push;
use function array_values;
use function assert;
use function class_exists;
use function file_get_contents;
use function ltrim;
use function Safe\preg_match;

final class PhpParserIterator extends ClassIterator
{
    use OfflineIteratorTrait;
    use RecursiveIteratorTrait;

    private const EXTENSION_PATTERN = '/\\.php$/i';

    private Parser $parser;
    private NodeTraverser $traverser;

    /** @param string[]|null $excludeNamespaces */
    public function __construct(string $path, int $flags = 0, array|null $excludeNamespaces = null, Closure|null $pathCallback = null)
    {
        $this->path = PathNormalizer::resolvePath($path);
        $this->parser = (new ParserFactory())->createForHostVersion();
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor($nameResolver = new NameResolver());
        if (class_exists(AnnotationParser::class)) {
            $this->traverser->addVisitor(new AnnotationParser($nameResolver));
        }

        parent::__construct($flags, $excludeNamespaces, $pathCallback);
    }

    protected function isInstantiable(mixed $reflector): bool
    {
        return $reflector instanceof Stmt\Class_ && ! $reflector->isAbstract();
    }

    protected function getGenerator(): Generator
    {
        $symbols = $files = [];

        foreach ($this->search() as $path => $info) {
            if (! preg_match(self::EXTENSION_PATTERN, $path, $m) || ! $info->isReadable()) {
                continue;
            }

            $code = file_get_contents($path);
            if ($code === false) {
                continue;
            }

            try {
                $stmts = $this->parser->parse($code);
            } catch (Error) {
                continue;
            }

            $nodeVisitor = new class extends NodeVisitorAbstract {
                /** @var Stmt\ClassLike[] */
                public array $reflections = [];

                public function leaveNode(Node $node): Node|null
                {
                    if (! ($node instanceof Stmt\ClassLike)) {
                        return null;
                    }

                    $this->reflections[] = $node;

                    return null;
                }
            };

            $this->traverser->addVisitor($nodeVisitor);
            $this->traverser->traverse($stmts ?? []);
            $this->traverser->removeVisitor($nodeVisitor);

            $fileSymbols = array_map(
                static function (Stmt\ClassLike $class) use (&$symbols): object {
                    return $symbols[(string) $class->namespacedName] = $class;
                },
                $nodeVisitor->reflections,
            );

            if (! $this->accept(PathNormalizer::resolvePath($path))) {
                continue;
            }

            $files[$path] = $fileSymbols;
        }

        foreach ($files as $path => $fileSymbols) {
            foreach ($fileSymbols as $fileSymbol) {
                if ($fileSymbol instanceof Stmt\Class_) {
                    $parents = [];
                    $interfaces = [];
                    $this->processInterfaces($interfaces, $fileSymbol->implements, $symbols);

                    $parent = $fileSymbol;
                    while (($parentName = $parent->extends)) {
                        $parent = $symbols[(string) $parentName] ?? null;
                        if ($parent === null) {
                            break; // We don't have information on the parent class.
                        }

                        assert($parent instanceof Stmt\Class_);
                        $parents[] = $parent;
                        $this->processInterfaces($interfaces, $parent->implements, $symbols);
                    }

                    $fileSymbol->setAttribute(Metadata::METADATA_KEY, new Metadata($path, [...$parents, ...array_values($interfaces)]));
                } elseif ($fileSymbol instanceof Stmt\Interface_) {
                    $interfaces = [];
                    $this->processInterfaces($interfaces, $fileSymbol->extends, $symbols);
                    $fileSymbol->setAttribute(Metadata::METADATA_KEY, new Metadata($path, array_values($interfaces)));
                } elseif ($fileSymbol instanceof Stmt\Trait_ || $fileSymbol instanceof Stmt\Enum_) {
                    $fileSymbol->setAttribute(Metadata::METADATA_KEY, new Metadata($path, []));
                }
            }

            yield from $this->processNodes($fileSymbols);
        }
    }

    /**
     * Processes classes array.
     *
     * @param Stmt\ClassLike[] $stmts
     *
     * @return Generator<string, Stmt\ClassLike>
     */
    private function processNodes(array $stmts): Generator
    {
        foreach ($stmts as $stmt) {
            $className = (string) ($stmt->namespacedName ?? $stmt->name);
            if (! $this->validNamespace($className)) {
                continue;
            }

            yield ltrim($className, '\\') => $stmt;
        }
    }

    /**
     * @param Stmt\Interface_[] $interfaces
     * @param Node\Name[] $parents
     * @param array<string, object> $symbols
     */
    private function processInterfaces(array &$interfaces, array $parents, array &$symbols): void
    {
        while ($parents) {
            $currentLevel = [];
            foreach ($parents as $parentName) {
                $p = $symbols[$parentName->toString()] ?? null;
                if (! $p instanceof Stmt\Interface_) {
                    continue;
                }

                $interfaces[$parentName->toString()] = $p;
                array_push($currentLevel, ...$p->extends);
            }

            $parents = $currentLevel;
        }
    }
}
