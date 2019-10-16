<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use Composer\Autoload\ClassLoader;

/**
 * Iterates over classes defined in a composer-generated ClassLoader.
 */
final class ComposerIterator extends ClassIterator
{
    /**
     * @var ClassLoader
     */
    private $classLoader;

    public function __construct(ClassLoader $classLoader, int $flags = 0)
    {
        $this->classLoader = $classLoader;

        parent::__construct($flags);
    }

    /**
     * {@inheritdoc}
     */
    protected function getGenerator(): \Generator
    {
        yield from $this->searchInClassMap();
        yield from $this->searchInPsrMap();
    }

    /**
     * Searches for class definitions in class map.
     *
     * @return \Generator
     */
    private function searchInClassMap(): \Generator
    {
        foreach ($this->classLoader->getClassMap() as $class => $file) {
            yield $class => new \ReflectionClass($class);
        }
    }

    /**
     * Iterates over psr-* maps and yield found classes.
     *
     * NOTE: If the class loader has been generated with ClassMapAuthoritative flag,
     * this method will not yield any element.
     *
     * @return \Generator
     */
    private function searchInPsrMap(): \Generator
    {
        if ($this->classLoader->isClassMapAuthoritative()) {
            // In this case, no psr-* map will be checked when autoloading classes.
            return;
        }

        foreach ($this->classLoader->getPrefixesPsr4() as $ns => $dirs) {
            foreach ($dirs as $dir) {
                yield from (new Psr4Iterator($ns, $dir, 0, $this->classLoader->getClassMap()));
            }
        }

        foreach ($this->classLoader->getPrefixes() as $ns => $dirs) {
            foreach ($dirs as $dir) {
                yield from (new Psr0Iterator($ns, $dir, 0, $this->classLoader->getClassMap()));
            }
        }
    }
}
