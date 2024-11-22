<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\Reflection;

use FilterIterator;
use Iterator;
use ReflectionClass;

use function array_filter;
use function array_map;
use function assert;
use function count;
use function method_exists;

/**
 * @template-covariant TValue of ReflectionClass
 * @template T of Iterator<class-string, TValue>
 * @template-extends FilterIterator<class-string, TValue, T>
 */
final class InterfaceImplementationFilterIterator extends FilterIterator
{
    /**
     * @param T $iterator
     * @param string[] $interfaces
     * @phpstan-param class-string[] $interfaces
     */
    public function __construct(Iterator $iterator, private readonly array $interfaces)
    {
        parent::__construct($iterator);
    }

    public function accept(): bool
    {
        $reflectionClass = $this->getInnerIterator()->current();
        assert($reflectionClass instanceof ReflectionClass);
        if (
            $reflectionClass->isInterface() ||
            $reflectionClass->isTrait() ||
            (method_exists($reflectionClass, 'isEnum') && $reflectionClass->isEnum())
        ) {
            return false;
        }

        return count(
            array_filter(
                array_map(
                    static fn (string $interface) => $reflectionClass->implementsInterface($interface),
                    $this->interfaces,
                ),
                static fn (bool $r) => $r === false,
            ),
        ) === 0;
    }
}
