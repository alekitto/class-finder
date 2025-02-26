<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Finder;

use IteratorAggregate;

interface FinderInterface extends IteratorAggregate
{
    /**
     * Filter by interface implementation.
     * If an array is passed, the class should implement all the interfaces
     * specified in the array.
     * Pass null to disable this filter.
     *
     * @param string|string[] $interface
     * @phpstan-param class-string|class-string[] $interface
     *
     * @return $this
     */
    public function implementationOf(string|array $interface): static;

    /**
     * Filters by class extension.
     * The class matches only if is an extension of $superClass.
     * Use null to disable this filter.
     *
     * @phpstan-param class-string|null $superClass
     *
     * @return $this
     */
    public function subclassOf(string|null $superClass): static;

    /**
     * Filters by a given annotation on the class.
     * The class matches only if a class annotation is present on the class.
     * The annotation target must be the class itself.
     *
     * @phpstan-param class-string|null $annotationClass
     *
     * @return $this
     */
    public function annotatedBy(string|null $annotationClass): static;

    /**
     * Filters by a given attribute on the class.
     * The class matches only if a PHP attribute is present on the class.
     * The attribute target must be the class itself.
     *
     * @phpstan-param class-string|null $attributeClass
     *
     * @return $this
     */
    public function withAttribute(string|null $attributeClass): static;

    /**
     * Adds a search directory.
     *
     * @param string|string[] $dirs
     *
     * @return $this
     */
    public function in(string|array $dirs): static;

    /**
     * Adds namespace(s) to search classes into.
     *
     * @param string|string[] $namespaces
     *
     * @return $this
     */
    public function inNamespace(string|array $namespaces): static;

    /**
     * Adds namespace(s) to exclude from search.
     *
     * @param string|string[] $namespaces
     *
     * @return $this
     */
    public function notInNamespace(string|array $namespaces): static;

    /**
     * Sets a custom callback for class filtering.
     * The callback will receive the class name as the only argument.
     *
     * @param callable(object, string):bool|null $callback
     *
     * @return $this
     */
    public function filter(callable|null $callback): static;

    /**
     * Adds rules that filenames must match.
     *
     * You can use patterns (delimited with / sign) or simple strings.
     *
     * $finder->path('some/special/dir')
     * $finder->path('/some\/special\/dir/') // same as above
     *
     * Use only / as dirname separator.
     *
     * @param string $pattern A pattern (a regexp or a string)
     *
     * @return $this
     */
    public function path(string $pattern): static;

    /**
     * Adds rules that filenames must not match.
     *
     * You can use patterns (delimited with / sign) or simple strings.
     *
     * $finder->notPath('some/special/dir')
     * $finder->notPath('/some\/special\/dir/') // same as above
     *
     * Use only / as dirname separator.
     *
     * @param string $pattern A pattern (a regexp or a string)
     *
     * @return $this
     */
    public function notPath(string $pattern): static;

    /**
     * Sets a custom callback for file filtering.
     * The callback will receive the full filepath as the only argument.
     *
     * @param callable(string):bool|null $callback
     *
     * @return $this
     */
    public function pathFilter(callable|null $callback): static;

    /**
     * Skips non-instantiable (abstract) classes, as well as interfaces and traits.
     *
     * @return $this
     */
    public function skipNonInstantiable(bool $skip = true): static;

    /**
     * Prevents the inclusion of files known to cause bugs and possible fatal errors.
     *
     * @return $this
     */
    public function skipBogonFiles(bool $skip = true): static;
}
