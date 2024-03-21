# ClassFinder

Utility classes to help discover other classes/namespaces

![Tests](https://github.com/alekitto/class-finder/workflows/Tests/badge.svg)
[![codecov](https://codecov.io/gh/alekitto/class-finder/branch/master/graph/badge.svg)](https://codecov.io/gh/alekitto/class-finder)

---

## Installation

```bash
$ composer require kcs/class-finder
```

## Usage

Finder helps you to discover classes into your project.

The most common way to discover is to use the provided `ComposerFinder`.
This will search for classes using the auto-generated class loader
from composer and resolving PSR-* namespaces accordingly.

Read more in the docs at [https://alekitto.github.io/class-finder/](https://alekitto.github.io/class-finder/)

### Basic usage

```php
use Kcs\ClassFinder\Finder\ComposerFinder;

$finder = new ComposerFinder();
foreach ($finder as $className => $reflector) {
    // Do magic things...
}
```

### Filtering

You can filter classes using the methods exposed by `FinderInterface`:

- `implementationOf(array $interfaces)`: Finds the classes that implements 
  all the given interfaces. You can pass a single interface as string.
- `subclassOf(string $superClass)`: Finds all the classes that are subclasses
  of the given class.
- `annontatedBy(string $annotationClass)`: Finds all the classes that have
  the given annotation in the class docblock.
- `withAttribute(string $attributeClass)`: Finds all the classes that have
  the given attribute applied on the class (PHP >= 8.0) only.
- `in(array $dirs)`: Searches only in given directories.
- `inNamespace(array $namespaces)`: Searches only in given namespaces.
- `filter(callable $callback)`: Custom filtering callback.
- `pathFilter(callable $callback)`: Custom filtering callback for loading files.


## License

This library is released under the MIT license.

## Contributions

Contributions are always welcome.
Please feel free to open a PR or file an issue.

---

Thank you for reading  
A.
