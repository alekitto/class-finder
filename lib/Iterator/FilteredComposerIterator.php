<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use Closure;
use Composer\Autoload\ClassLoader;
use Generator;
use Kcs\ClassFinder\PathNormalizer;
use Kcs\ClassFinder\Reflection\NativeReflectorFactory;
use Kcs\ClassFinder\Reflection\ReflectorFactoryInterface;
use Kcs\ClassFinder\Util\ErrorHandler;
use Throwable;

use function array_map;
use function array_unique;
use function explode;
use function strpos;

/**
 * Iterates over classes defined in a composer-generated ClassLoader
 * and do some dirty filtering.
 *
 * This class is for internal use.
 * Do not use it directly: its filters are not precise enough, and
 * would lead to bad results.
 *
 * @internal
 */
final class FilteredComposerIterator extends ClassIterator
{
    private ReflectorFactoryInterface $reflectorFactory;

    /** @var string[]|null */
    private array|null $namespaces = null;

    /** @var string[]|null */
    private array|null $notNamespaces = null;

    /** @var string[]|null */
    private array|null $dirs;

    /**
     * @param string[]|null $namespaces
     * @param string[]|null $notNamespaces
     * @param string[]|null $dirs
     */
    public function __construct(
        private readonly ClassLoader $classLoader,
        ReflectorFactoryInterface|null $reflectorFactory,
        array|null $namespaces,
        array|null $notNamespaces,
        array|null $dirs,
        int $flags = 0,
        Closure|null $pathCallback = null,
    ) {
        $this->reflectorFactory = $reflectorFactory ?? new NativeReflectorFactory();
        $this->dirs = $dirs !== null ? array_map(PathNormalizer::class . '::resolvePath', $dirs) : $dirs;

        if ($namespaces !== null) {
            $namespaces = array_map(static function ($ns) {
                return explode('\\', $ns, 2)[0];
            }, $namespaces);

            $this->namespaces = array_unique($namespaces);
        }

        if ($notNamespaces !== null) {
            $this->notNamespaces = array_unique($notNamespaces);
        }

        parent::__construct($flags, $pathCallback);
    }

    protected function getGenerator(): Generator
    {
        yield from $this->searchInClassMap();
        yield from $this->searchInPsrMap();
    }

    /**
     * Searches for class definitions in class map.
     */
    private function searchInClassMap(): Generator
    {
        /** @var class-string $class */
        foreach ($this->classLoader->getClassMap() as $class => $file) {
            if (! $this->validNamespace($class)) {
                continue;
            }

            $file = PathNormalizer::resolvePath($file);
            if (! $this->validDir($file)) {
                continue;
            }

            if ($this->pathCallback && ! ($this->pathCallback)($file)) {
                continue;
            }

            ErrorHandler::register();
            try {
                $reflectionClass = $this->reflectorFactory->reflect($class);
            } catch (Throwable) { /** @phpstan-ignore-line */
                continue;
            } finally {
                ErrorHandler::unregister();
            }

            yield $class => $reflectionClass;
        }
    }

    /**
     * Iterates over psr-* maps and yield found classes.
     *
     * NOTE: If the class loader has been generated with ClassMapAuthoritative flag,
     * this method will not yield any element.
     */
    private function searchInPsrMap(): Generator
    {
        if ($this->classLoader->isClassMapAuthoritative()) {
            // In this case, no psr-* map will be checked when autoloading classes.
            return;
        }

        foreach ($this->traversePrefixes($this->classLoader->getPrefixesPsr4()) as $ns => $dir) {
            yield from new Psr4Iterator($ns, $dir, $this->reflectorFactory, $this->flags, $this->classLoader->getClassMap(), $this->notNamespaces, $this->pathCallback);
        }

        foreach ($this->traversePrefixes($this->classLoader->getPrefixes()) as $ns => $dir) {
            yield from new Psr0Iterator($ns, $dir, $this->reflectorFactory, $this->flags, $this->classLoader->getClassMap(), $this->notNamespaces, $this->pathCallback);
        }
    }

    /** @param array<string, string[]|string> $prefixes */
    private function traversePrefixes(array $prefixes): Generator
    {
        foreach ($prefixes as $ns => $dirs) {
            if (! $this->validNamespace($ns)) {
                continue;
            }

            $dirs = (array) $dirs;
            foreach ($dirs as $dir) {
                $dir = PathNormalizer::resolvePath($dir);
                if (! $this->validDir($dir)) {
                    continue;
                }

                yield $ns => $dir;
            }
        }
    }

    private function validNamespace(string $class): bool
    {
        if ($this->notNamespaces !== null) {
            foreach ($this->notNamespaces as $namespace) {
                if (strpos($class, $namespace) === 0) {
                    return false;
                }
            }
        }

        if ($this->namespaces === null) {
            return true;
        }

        foreach ($this->namespaces as $namespace) {
            if (strpos($class, $namespace) === 0) {
                return true;
            }
        }

        return false;
    }

    private function validDir(string $path): bool
    {
        if ($this->dirs === null) {
            return true;
        }

        foreach ($this->dirs as $dir) {
            // Check for intersection of required path and evaluated dir
            if (strpos($path, $dir) !== false || strpos($dir, $path) !== false) {
                return true;
            }
        }

        return false;
    }
}
