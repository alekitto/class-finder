# ClassFinder

Utility classes to help discover other classes/namespaces

[![Build Status](https://travis-ci.org/alekitto/class-finder.svg?branch=master)](https://travis-ci.org/alekitto/class-finder)
[![Code Coverage](https://scrutinizer-ci.com/g/alekitto/class-finder/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/alekitto/class-finder/?branch=master)

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

### Basic usage

```php
$finder = new ComposerFinder();
foreach ($finder as $className => $path) {
    // Do magic things...
}
```

### Filtering

You can filter classes using the methods exposed by `FinderInterface`:

- `implementationOf(array $interfaces)`: Finds the classes that implements 
  all the given interfaces. You can pass a single interface as string.
- `subclassOf(string $superClass)`: Finds all the classes that are subclasses
  of the given class.
- `in(array $dirs)`: Searches only in given directories.
- `inNamespace(array $namespaces)`: Searches only in given namespaces.
- `filter(callable $callback)`: Custom filtering callback.

## License

This library is released under the MIT license.

## Contributions

Contributions are always welcome.
Please feel free to open a PR or file an issue.

---

Thank you for reading
