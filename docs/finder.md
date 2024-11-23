# Finder

A finder finds classes, interfaces and traits based on different criteria (namespace, location, attributes, annotations, etc.) via an intuitive fluent interface.

?> `Finder` interface is inspired by Symfony finder component.

## Available finders

The following finders are available in this library:

- `ComposerFinder` - detects a composer `ClassLoader` and uses its class-map and psr-0/4 autoloader rules to filter and find all the installed classes.
- `Psr0Finder` - finds classes into a psr-0 folder.
- `Psr4Finder` - finds classes into a psr-4 folder.
- `RecursiveFinder` - recursive search PHP files into a folder. Note: this finder includes the files with `require_once` and uses `get_declared_classes/interfaces/traits` to enumerate the symbols.
- `PhpDocumentorFinder` - analyzes a directory with `phpdocumentor/reflection` package (version 4, 5 or 6) to extract the symbols declared in the PHP files.
- `PhpParserFinder` - analyzes a directory with `nikic/php-parser` package (version 4) to parse PHP files and find the declared symbols.
- `ClassMapFinder` - uses a classmap array and finds classes contained into the map.

All the finders implement the `Kcs\ClassFinder\Finder\FinderInterface` interface.

### Finders are iterable

All the finders are iterable which yields key/value tuple:

- The key *always* contains the fully-qualified class name as string
- The value is a reflector object. This can vary upon the used finder. In particular:
  - `ComposerFinder`, `Psr0Finder`, `Psr4Finder` and `RecursiveFinder` yield PHP's `Reflector` objects (`ReflectionClass`)
  - `PhpDocumentorFinder` yield instances of `phpDocumentor\Reflection\Php\Class_`, `phpDocumentor\Reflection\Php\Interface_`, `phpDocumentor\Reflection\Php\Trait_` and `phpDocumentor\Reflection\Php\Enum_`
  - `PhpParserFinder` yield instances of `PhpParser\Node\Stmt\ClassLike`

Additionally `ComposerFinder` and `Psr*Finder` expose a `setReflectorFactory` method which can be used to customize the reflector object creation.  
The only available implementation of the reflector factory (`NativeReflectorFactory`) *always* return a `ReflectionClass` object.

## Finder criteria

Criteria can be added to the finder using the following methods:

- `implementationOf(array|string $interfaces)` - Only the classes that implements all the given interfaces will be yielded. You can pass a single interface as string.
- `subclassOf(string $superClass)` - Only the classes that are subclasses of the given class will be yielded.
- `annontatedBy(string $annotationClass)` - Finds all the classes that have the given annotation in the class docblock.
- `withAttribute(string $attributeClass)` - Finds all the classes that have the given attribute applied on the class (PHP >= 8.0) only.
- `in(array $dirs)` - Searches only in given directories.
- `inNamespace(array $namespaces)` -  Searches only in given namespaces.
- `notInNamespace(array $namespaces)` -  Searches only *outside* the given namespaces.
- `path(string $pattern)` - Adds a filter based on file pathname. If starts with '/' will be interpreted as a regex.
- `notPath(string $pattern)` - Adds a negative filter based on file pathname. If starts with '/' will be interpreted as a regex.
- `filter(callable $callback)` - Adds a custom filter callback.
- `pathFilter(callable $callback)` - Adds a custom file pathname filter callback.
- `skipNonInstantiable(bool $skip = true)` - Whether to skip or not abstract classes, traits and interfaces.
- `skipBogonFiles(bool $skip = true)` - Prevents the inclusion of files known to cause bugs and possible fatal errors.

## Offline finders

There are two "offline" finders: `PhpDocumentorFinder` and `PhpParserFinder` which analyse the php files searching for classes/interfaces/traits/enums without including them.
These finders are slower can be useful in case you don't want to execute PHP files (untrusted sources, possibly invalid files, etc.)

Some limitations apply:

- `PhpParserFinder` uses a custom annotation reader, not fully tested, filtering by annotations could be buggy
- `PhpDocumentorFinder` will skip classes with invalid phpdoc tags
- offline finders will skip invalid files/classes if there's a syntax error
- superclass and interface implementation filtering could be incomplete: all the symbols must be known to build a full class chain.
  If a class in the chain is unknown, the finders cannot calculate interface implementations and class chain correctly.
  To correctly calculate the class chain for extensions/core subclasses, you need to install stubs file (ex: using `jetbrains/phpstorm-stubs` package).
