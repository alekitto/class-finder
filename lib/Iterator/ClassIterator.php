<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

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

    /**
     * @var null|callable
     */
    private $_apply;

    /**
     * @var mixed
     */
    private $_currentElement;

    /**
     * @var mixed
     */
    private $_current;

    public function __construct(int $flags = 0)
    {
        $this->flags = $flags;
        $this->apply(null);
        $this->rewind();
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        if (! $this->valid()) {
            return null;
        }

        if (null === $this->_current) {
            $this->_current = call_user_func($this->_apply, $this->_currentElement);
        }

        return $this->_current;
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
    {
        $generator = $this->generator();
        $valid = false;

        while (! $valid && $generator->valid()) {
            $generator->next();

            $this->_current = null;
            $this->_currentElement = $generator->current();
            $valid = $this->filter();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->generator()->key();
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        return $this->generator()->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->foundClasses = [];
        $this->generator = null;

        $this->_currentElement = $this->generator()->current();
    }

    /**
     * Registers a callable to apply to each element of the iterator.
     *
     * @param callable $func
     *
     * @return $this
     */
    public function apply(callable $func = null): self
    {
        if (null === $func) {
            $func = function ($val) {
                return $val;
            };
        }

        $this->_current = null;
        $this->_apply = $func;

        return $this;
    }

    /**
     * Returns a generator to iterate classes over.
     * Yielded elements must have the class name as key
     * and the reflector as its value.
     *
     * @return \Generator
     */
    abstract protected function getGenerator(): \Generator;

    /**
     * Checks whether the given class is instantiable.
     *
     * @param \ReflectionClass $reflector
     *
     * @return bool
     */
    protected function isInstantiable($reflector): bool
    {
        return $reflector->isInstantiable();
    }

    /**
     * Do some basic validity checks on the given class.
     * Returns FALSE if the class is not valid or already
     * returned by this iterator, TRUE otherwise.
     *
     * @return bool
     */
    private function filter(): bool
    {
        if (null === $this->_currentElement) {
            // End of the generator.
            return false;
        }

        $className = $this->generator()->key();
        if (isset($this->foundClasses[$className])) {
            return false;
        }

        $this->foundClasses[$className] = true;
        if ($this->flags & self::SKIP_NON_INSTANTIABLE && ! $this->isInstantiable($this->_currentElement)) {
            return false;
        }

        return true;
    }

    private function generator(): \Generator
    {
        if (null === $this->generator) {
            $this->generator = $this->getGenerator();
            $this->_currentElement = $this->generator->current();
            if (! $this->filter()) {
                $this->next();
            }
        }

        return $this->generator;
    }
}
