<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Finder;

use Composer\Autoload\ClassLoader;
use Iterator;
use Kcs\ClassFinder\Iterator\ComposerIterator;
use Kcs\ClassFinder\Iterator\FilteredComposerIterator;
use Kcs\ClassFinder\Reflection\ReflectorFactoryInterface;
use Reflector;
use RuntimeException;
use Symfony\Component\ErrorHandler\DebugClassLoader;

use function class_exists;
use function is_array;
use function spl_autoload_functions;

/**
 * Finds classes/namespaces using the registered autoloader by composer.
 */
final class ComposerFinder implements FinderInterface
{
    use ReflectionFilterTrait;

    private ClassLoader $loader;
    private ?ReflectorFactoryInterface $reflectorFactory = null;

    public function __construct(?ClassLoader $loader = null)
    {
        $this->loader = $loader ?? self::getValidLoader();
    }

    public function setReflectorFactory(?ReflectorFactoryInterface $reflectorFactory): self
    {
        $this->reflectorFactory = $reflectorFactory;

        return $this;
    }

    /**
     * @return Iterator<Reflector>
     */
    public function getIterator(): Iterator
    {
        if ($this->namespaces || $this->dirs || $this->notNamespaces) {
            $iterator = new FilteredComposerIterator($this->loader, $this->reflectorFactory, $this->namespaces, $this->notNamespaces, $this->dirs);
        } else {
            $iterator = new ComposerIterator($this->loader, $this->reflectorFactory);
        }

        return $this->applyFilters($iterator);
    }

    /**
     * Try to get a registered instance of composer ClassLoader.
     *
     * @throws RuntimeException if composer CLassLoader cannot be found.
     */
    private static function getValidLoader(): ClassLoader
    {
        foreach (spl_autoload_functions() as $autoloadFn) {
            if (is_array($autoloadFn) && class_exists(DebugClassLoader::class) && $autoloadFn[0] instanceof DebugClassLoader) {
                $autoloadFn = $autoloadFn[0]->getClassLoader();
            }

            if (is_array($autoloadFn) && $autoloadFn[0] instanceof ClassLoader) {
                return $autoloadFn[0];
            }
        }

        throw new RuntimeException('Cannot find a valid composer class loader in registered autoloader functions. Cannot continue.');
    }
}
