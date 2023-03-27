<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use Generator;
use Iterator;
use ReflectionClass;

use function assert;
use function call_user_func;
use function is_string;

/**
 * Abstract class iterator.
 *
 * This could be used to implement other iterators,
 * as it does some checks for you, like class existence,
 * duplicate skipping and non-instantiable filtering.
 */
abstract class ClassIterator implements Iterator
{
    public const SKIP_NON_INSTANTIABLE = 1;

    private Generator|null $generator = null;

    /** @var array<string, bool> */
    private array $foundClasses = [];

    /** @var callable */
    private $_apply;

    private mixed $_currentElement;
    private mixed $_current = null;

    public function __construct(private int $flags = 0)
    {
        $this->apply(null);
        $this->rewind();
    }

    public function current(): mixed
    {
        if (! $this->valid()) {
            return null;
        }

        if ($this->_current === null) {
            $this->_current = call_user_func($this->_apply, $this->_currentElement);
        }

        return $this->_current;
    }

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

    public function key(): int|string
    {
        return $this->generator()->key();
    }

    public function valid(): bool
    {
        return $this->generator()->valid();
    }

    public function rewind(): void
    {
        $this->foundClasses = [];
        $this->generator = null;

        $this->_currentElement = $this->generator()->current();
    }

    /**
     * Registers a callable to apply to each element of the iterator.
     *
     * @return $this
     */
    public function apply(callable|null $func = null): self
    {
        if ($func === null) {
            $func = static function ($val) {
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
     */
    abstract protected function getGenerator(): Generator;

    /**
     * Checks whether the given class is instantiable.
     */
    protected function isInstantiable(mixed $reflector): bool
    {
        return $reflector instanceof ReflectionClass && $reflector->isInstantiable();
    }

    /**
     * Do some basic validity checks on the given class.
     * Returns FALSE if the class is not valid or already
     * returned by this iterator, TRUE otherwise.
     */
    private function filter(): bool
    {
        if ($this->_currentElement === null) {
            // End of the generator.
            return false;
        }

        $className = $this->generator()->key();
        assert(is_string($className));
        if (isset($this->foundClasses[$className])) {
            return false;
        }

        $this->foundClasses[$className] = true;

        return ! ($this->flags & self::SKIP_NON_INSTANTIABLE) || $this->isInstantiable($this->_currentElement);
    }

    private function generator(): Generator
    {
        if ($this->generator === null) {
            $this->generator = $this->getGenerator();
            $this->_currentElement = $this->generator->current();
            if (! $this->filter()) {
                $this->next();
            }
        }

        assert($this->generator !== null);

        return $this->generator;
    }
}
