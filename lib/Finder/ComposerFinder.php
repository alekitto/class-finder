<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Finder;

use Composer\Autoload\ClassLoader;
use Iterator;
use Kcs\ClassFinder\Iterator\ClassIterator;
use Kcs\ClassFinder\Iterator\ComposerIterator;
use Kcs\ClassFinder\Iterator\FilteredComposerIterator;
use Kcs\ClassFinder\Reflection\ReflectorFactoryInterface;
use Kcs\ClassFinder\Util\BogonFilesFilter;
use Reflector;
use RuntimeException;
use Symfony\Component\ErrorHandler\DebugClassLoader;

use function array_combine;
use function array_search;
use function class_exists;
use function file_exists;
use function is_array;
use function spl_autoload_functions;

/**
 * Finds classes/namespaces using the registered autoloader by composer.
 */
final class ComposerFinder implements FinderInterface
{
    use ReflectionFilterTrait;
    use RecursiveFinderTrait;

    private ClassLoader $loader;
    private ReflectorFactoryInterface|null $reflectorFactory = null;

    /** @var array<string, string> */
    private array $files;
    private bool $useAutoloading = true;

    public function __construct(ClassLoader|null $loader = null)
    {
        $this->loader = $loader ?? self::getValidLoader();
        $vendorDir = array_search($this->loader, ClassLoader::getRegisteredLoaders());

        if ($vendorDir === false) {
            return;
        }

        $autoloadFilesFn = $vendorDir . '/composer/autoload_files.php';
        if (! file_exists($autoloadFilesFn)) {
            return;
        }

        $files = include $autoloadFilesFn;
        if (! is_array($files)) {
            return;
        }

        $this->files = array_combine($files, $files);
    }

    public function setReflectorFactory(ReflectorFactoryInterface|null $reflectorFactory): self
    {
        $this->reflectorFactory = $reflectorFactory;

        return $this;
    }

    public function useAutoloading(bool $use = true): self
    {
        $this->useAutoloading = $use;

        return $this;
    }

    /** @return Iterator<class-string, Reflector> */
    public function getIterator(): Iterator
    {
        $flags = 0;
        if ($this->skipNonInstantiable) {
            $flags |= ClassIterator::SKIP_NON_INSTANTIABLE;
        }

        $pathFilterCallback = $this->pathFilterCallback ? ($this->pathFilterCallback)(...) : null;
        if ($this->useAutoloading) {
            $flags |= ClassIterator::USE_AUTOLOADING;

            $pathFilterCallback ??= static fn () => true;
            $pathFilterCallback = function (string $path) use ($pathFilterCallback): bool {
                if (isset($this->files[$path])) {
                    return false;
                }

                return $pathFilterCallback($path);
            };
        }

        if ($this->skipBogonClasses) {
            $pathFilterCallback = BogonFilesFilter::getFileFilterFn($pathFilterCallback);
        }

        if ($this->namespaces || $this->dirs || $this->notNamespaces) {
            $iterator = new FilteredComposerIterator(
                $this->loader,
                $this->reflectorFactory,
                $this->namespaces,
                $this->notNamespaces,
                $this->dirs,
                $flags,
                $pathFilterCallback,
            );
        } else {
            $iterator = new ComposerIterator(
                $this->loader,
                $this->reflectorFactory,
                $flags,
                $pathFilterCallback,
            );
        }

        if (isset($this->fileFinder)) {
            $iterator->setFileFinder($this->fileFinder);
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
