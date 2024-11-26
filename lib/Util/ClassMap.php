<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Util;

use IteratorAggregate;
use Kcs\ClassFinder\Finder\ClassMapFinder;
use Kcs\ClassFinder\Finder\FinderInterface;
use Kcs\ClassFinder\Iterator\ClassMapIterator;
use Kcs\ClassFinder\PathNormalizer;
use Kcs\ClassFinder\Util\Offline\Metadata;
use Kcs\ClassFinder\Util\PhpDocumentor\MetadataRegistry;
use phpDocumentor\Reflection\Php\Class_;
use phpDocumentor\Reflection\Php\Enum_;
use phpDocumentor\Reflection\Php\Interface_;
use phpDocumentor\Reflection\Php\Trait_;
use PhpParser\Node\Stmt\ClassLike;
use ReflectionClass;
use Roave\BetterReflection;
use RuntimeException;
use Traversable;

use function array_map;
use function array_shift;
use function assert;
use function count;
use function explode;
use function implode;
use function method_exists;
use function rtrim;
use function str_pad;

use const DIRECTORY_SEPARATOR;

final class ClassMap implements IteratorAggregate
{
    /** @var array<class-string, string> */
    private array $map = [];

    private function __construct()
    {
    }

    public static function fromFinder(FinderInterface $finder): self
    {
        $classMap = new self();

        /** @var class-string $className */
        foreach ($finder as $className => $reflector) {
            if ($reflector instanceof ReflectionClass || $reflector instanceof BetterReflection\Reflection\ReflectionClass) {
                $filename = $reflector->getFileName();
            } elseif ($reflector instanceof ClassLike) {
                $metadata = $reflector->getAttribute(Metadata::METADATA_KEY);
                assert($metadata instanceof Metadata || $metadata === null);
                $filename = $metadata?->filePath;
            } elseif ($reflector instanceof Class_ || $reflector instanceof Interface_ || $reflector instanceof Trait_ || $reflector instanceof Enum_) {
                $metadata = method_exists($reflector, 'getMetadata') ? $reflector->getMetadata() : MetadataRegistry::getInstance()->getMetadata($reflector);
                $metadata = $metadata[Metadata::METADATA_KEY] ?? null;
                assert($metadata instanceof Metadata || $metadata === null);
                $filename = $metadata?->filePath;
            } else {
                throw new RuntimeException('Cannot create classmap from reflector class ' . $reflector::class);
            }

            if ($filename === null || $filename === false) {
                continue;
            }

            $classMap->map[$className] = PathNormalizer::resolvePath($filename);
        }

        return $classMap;
    }

    public function createFinder(): ClassMapFinder
    {
        return new ClassMapFinder($this->map);
    }

    /** @return array<class-string, string> */
    public function getMap(string|null $relativeTo = null): array
    {
        if ($relativeTo === null) {
            return $this->map;
        }

        return array_map(static fn (string $fn) => self::relativePath($relativeTo, $fn), $this->map);
    }

    /** @return Traversable<class-string, ReflectionClass> */
    public function getIterator(): Traversable
    {
        return new ClassMapIterator($this->map, null);
    }

    private static function relativePath(string $from, string $to): string
    {
        $from = PathNormalizer::resolvePath($from);

        $from = explode(DIRECTORY_SEPARATOR, rtrim($from, DIRECTORY_SEPARATOR));
        $to = explode(DIRECTORY_SEPARATOR, rtrim($to, DIRECTORY_SEPARATOR));

        while (count($from) && count($to) && $from[0] === $to[0]) {
            array_shift($from);
            array_shift($to);
        }

        return str_pad('', count($from) * 3, '..' . DIRECTORY_SEPARATOR) . implode(DIRECTORY_SEPARATOR, $to);
    }
}
