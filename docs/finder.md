# Finder

A finder finds classes, interfaces and traits based on different criteria (namespace, location,
attributes, annotations, etc) via an intuitive fluent interface.

?> `Finder` interface is inspired by Symfony finder component.

## Available finders

The following finders are available in this library:

- `ComposerFinder` - detects a composer `ClassLoader` and uses its class-map and psr-0/4 autoloader rules
  to filter and find all the installed classes.
- `Psr0Finder` - finds classes into a psr-0 folder.
- `Psr4Finder` - finds classes into a psr-4 folder.
- `RecursiveFinder` - recursive search PHP files into a folder. Note: this finder includes the
  files with `require_once` and uses `get_declared_classes/interfaces/traits` to enumerate the symbols.
- `PhpDocumentorFinder` - analyzes a directory with `phpdocumentor/reflection` package (version 4) to extract the
  symbols declared in the PHP files.

All the finders implement the `Kcs\ClassFinder\Finder\FinderInterface` interface.

### Finders are iterable

All the finders are iterable which yields key/value tuple:

- The key *always* contains the fully-qualified class name as string
- The value is a reflector object. This can vary upon the used finder. In particular:
  - `ComposerFinder`, `Psr0Finder`, `Psr4Finder` and `RecursiveFinder` yield PHP's `Reflector` objects (`ReflectionClass`)
  - `PhpDocumentorFinder` yield instances of `phpDocumentor\Reflection\Php\Class_`

Additionally `ComposerFinder` and `Psr*Finder` expose a `setReflectorFactory` method which can be
used to customize the reflector object creation.  
The only available implementation of the reflector factory (`NativeReflectorFactory`) *always* return
a `ReflectionClass` object.

## Finder criteria

Criteria can be added to the finder using the following methods:

- `implementationOf(array $interfaces)` - Only the classes that implements all the given interfaces will be yielded.
  You can pass a single interface as string.
- `subclassOf(string $superClass)` - Only the classes that are subclasses of the given class will be yielded.
- `annontatedBy(string $annotationClass)` - Finds all the classes that have the given annotation in the class docblock.
- `withAttribtue(string $attributeClass)` - Finds all the classes that have the given attribute applied on the 
  class (PHP >= 8.0) only. NOTE: This will not work with php documentor finder as attributes support has been not
  implemented yet ([here's the relevant issue](https://github.com/phpDocumentor/Reflection/issues/185))
- `in(array $dirs)` - Searches only in given directories.
- `inNamespace(array $namespaces)` -  Searches only in given namespaces.
- `notInNamespace(array $namespaces)` -  Searches only *outside* the given namespaces.
- `filter(callable $callback)` - Adds a custom filter callback.

