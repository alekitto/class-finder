<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Finder;

interface FinderInterface extends \IteratorAggregate
{
    /**
     * Filter by interface implementation.
     * If an array is passed, the class should implement all the interfaces
     * specified in the array.
     * Pass null to disable this filter.
     *
     * @param string|string[] $interface
     *
     * @return FinderInterface
     */
    public function implementationOf($interface): self;

    /**
     * Filters by class extension.
     * The class matches only if is an extension of $superClass.
     * Use null to disable this filter.
     *
     * @param string $superClass
     *
     * @return FinderInterface
     */
    public function subclassOf(?string $superClass): self;

    /**
     * Adds a search directory.
     *
     * @param string|string[] $dirs
     *
     * @return FinderInterface
     */
    public function in($dirs): self;

    /**
     * Adds namespace(s) to search classes into.
     *
     * @param string|string[] $namespaces
     *
     * @return FinderInterface
     */
    public function inNamespace($namespaces): self;

    /**
     * Sets a custom callback for class filtering.
     * The callback will receive the class name as the only argument.
     *
     * @param callable $callback
     *
     * @return FinderInterface
     */
    public function filter(?callable $callback): self;
}
