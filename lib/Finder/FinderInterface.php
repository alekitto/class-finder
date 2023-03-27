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
    public function implementationOf(string|array $interface): self;

    /**
     * Filters by class extension.
     * The class matches only if is an extension of $superClass.
     * Use null to disable this filter.
     *
     * @phpstan-param class-string|null $superClass
     *
     * @return $this
     */
    public function subclassOf(string|null $superClass): self;

    /**
     * Filters by a given annotation on the class.
     * The class matches only if a class annotation is present on the class.
     * The annotation target must be the class itself.
     *
     * @phpstan-param class-string|null $annotationClass
     *
     * @return $this
     */
    public function annotatedBy(string|null $annotationClass): self;

    /**
     * Filters by a given attribute on the class.
     * The class matches only if a PHP attribute is present on the class.
     * The attribute target must be the class itself.
     *
     * @phpstan-param class-string|null $attributeClass
     *
     * @return $this
     */
    public function withAttribute(string|null $attributeClass): self;

    /**
     * Adds a search directory.
     *
     * @param string|string[] $dirs
     *
     * @return $this
     */
    public function in(string|array $dirs): self;

    /**
     * Adds namespace(s) to search classes into.
     *
     * @param string|string[] $namespaces
     *
     * @return $this
     */
    public function inNamespace(string|array $namespaces): self;

    /**
     * Adds namespace(s) to exclude from search.
     *
     * @param string|string[] $namespaces
     *
     * @return $this
     */
    public function notInNamespace(string|array $namespaces): self;

    /**
     * Sets a custom callback for class filtering.
     * The callback will receive the class name as the only argument.
     *
     * @return $this
     */
    public function filter(callable|null $callback): self;

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
    public function path(string $pattern): self;

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
    public function notPath(string $pattern): self;
}
