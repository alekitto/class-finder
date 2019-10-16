<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Finder;

use Composer\Autoload\ClassLoader;
use Kcs\ClassFinder\Iterator\ComposerIterator;
use Kcs\ClassFinder\Iterator\FilteredComposerIterator;
use Symfony\Component\Debug\DebugClassLoader;

/**
 * Finds classes/namespaces using the registered autoloader by composer.
 */
final class ComposerFinder implements FinderInterface
{
    use ReflectionFilterTrait;

    /**
     * @var ClassLoader
     */
    private $loader;

    public function __construct(ClassLoader $loader = null)
    {
        if (null === $loader) {
            $loader = self::getValidLoader();
        }

        $this->loader = $loader;
    }

    public function getIterator(): \Iterator
    {
        if ($this->namespaces || $this->dirs) {
            $iterator = new FilteredComposerIterator($this->loader, $this->namespaces, $this->dirs);
        } else {
            $iterator = new ComposerIterator($this->loader);
        }

        return $this->applyFilters($iterator);
    }

    /**
     * Try to get a registered instance of composer ClassLoader.
     *
     * @return ClassLoader
     *
     * @throws \RuntimeException if composer CLassLoader cannot be found
     */
    private static function getValidLoader(): ClassLoader
    {
        foreach (\spl_autoload_functions() as $autoload_function) {
            if (\is_array($autoload_function) && $autoload_function[0] instanceof DebugClassLoader) {
                $autoload_function = $autoload_function[0]->getClassLoader();
            }

            if (\is_array($autoload_function) && $autoload_function[0] instanceof ClassLoader) {
                return $autoload_function[0];
            }
        }

        throw new \RuntimeException('Cannot find a valid composer class loader in registered autoloader functions. Cannot continue.');
    }
}
