<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Finder\Iterator;

/**
 * Abstract class iterator.
 *
 * This could be used to implement other iterators,
 * as it does some checks for you, like class existence,
 * duplicate skipping and non-instantiable filtering.
 */
abstract class ClassIterator implements \Iterator
{
    const SKIP_NON_INSTANTIABLE = 1;

    /**
     * @var \Generator
     */
    private $generator = null;

    /**
     * @var string[]
     */
    private $foundClasses = [];

    /**
     * @var int
     */
    private $flags = 0;

    public function __construct(int $flags = 0)
    {
        $this->flags = $flags;
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return $this->generator()->current();
    }

    /**
     * @inheritdoc
     */
    public function next(): void
    {
        $generator = $this->generator();
        $valid = false;

        while (! $valid && $generator->valid()) {
            $generator->next();
            $valid = $this->filter($generator->key());
        }
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->generator()->key();
    }

    /**
     * @inheritdoc
     */
    public function valid(): bool
    {
        return $this->generator()->valid();
    }

    /**
     * @inheritdoc
     */
    public function rewind(): void
    {
        $this->foundClasses = [];
        $this->generator = null;
    }

    /**
     * Returns a generator to iterate classes over.
     * Yielded elements must have the class name as key
     * and the path containing it as value.
     *
     * @return \Generator
     */
    abstract protected function getGenerator(): \Generator;

    /**
     * Checks whether the given class is instantiable.
     *
     * @param string $className
     *
     * @return bool
     */
    protected function isInstantiable(string $className): bool
    {
        try {
            $reflClass = new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
            return false;
        }

        return $reflClass->isInstantiable();
    }

    /**
     * Checks whether the given class exists.
     *
     * @param string $className
     *
     * @return bool
     */
    protected function exists(string $className): bool
    {
        try {
            $reflClass = new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
            return false;
        }

        return true;
    }

    /**
     * Do some basic validity checks on the given class.
     * Returns FALSE if the class is not valid or already
     * returned by this iterator, TRUE otherwise.
     *
     * @param null|string $className
     *
     * @return bool
     */
    private function filter(?string $className): bool
    {
        if (null === $className) {
            // End of the generator.
            return false;
        }

        if (isset($this->foundClasses[$className])) {
            return false;
        }

        $this->foundClasses[$className] = true;

        if (! $this->exists($className)) {
            return false;
        }

        if ($this->flags & self::SKIP_NON_INSTANTIABLE && ! $this->isInstantiable($className)) {
            return false;
        }

        return true;
    }

    private function generator(): \Generator
    {
        if (null === $this->generator) {
            $this->generator = $this->getGenerator();
            if (! $this->filter($this->generator->key())) {
                $this->next();
            }
        }

        return $this->generator;
    }
}
