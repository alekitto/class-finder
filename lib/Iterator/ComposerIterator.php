<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use Composer\Autoload\ClassLoader;
use Generator;
use Kcs\ClassFinder\Reflection\NativeReflectorFactory;
use Kcs\ClassFinder\Reflection\ReflectorFactoryInterface;
use Kcs\ClassFinder\Util\ErrorHandler;
use Throwable;

/**
 * Iterates over classes defined in a composer-generated ClassLoader.
 */
final class ComposerIterator extends ClassIterator
{
    private ClassLoader $classLoader;
    private ReflectorFactoryInterface $reflectorFactory;

    public function __construct(ClassLoader $classLoader, ?ReflectorFactoryInterface $reflectorFactory = null, int $flags = 0)
    {
        $this->classLoader = $classLoader;
        $this->reflectorFactory = $reflectorFactory ?? new NativeReflectorFactory();

        parent::__construct($flags);
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
        foreach ($this->classLoader->getClassMap() as $class => $file) {
            ErrorHandler::register();
            try {
                $reflectionClass = $this->reflectorFactory->reflect($class);
            } catch (Throwable $e) { /** @phpstan-ignore-line */
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

        foreach ($this->classLoader->getPrefixesPsr4() as $ns => $dirs) {
            foreach ($dirs as $dir) {
                yield from new Psr4Iterator($ns, $dir, $this->reflectorFactory, 0, $this->classLoader->getClassMap());
            }
        }

        foreach ($this->classLoader->getPrefixes() as $ns => $dirs) {
            foreach ($dirs as $dir) {
                yield from new Psr0Iterator($ns, $dir, $this->reflectorFactory, 0, $this->classLoader->getClassMap());
            }
        }
    }
}
