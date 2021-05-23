# Class Finder
__Discover classes in your PHP project__

## Introduction

Class Finder provides helpers and utilities to find, filter and enumerate classes, interfaces and traits
in your PHP projects analyzing files statically or at runtime.

## Installation

```bash
$ composer require kcs/class-finder
```

## Usage

A `Finder` is an object implementing `FinderInterface` which is iterable and exposes some convenient methods
to filter, exclude or restrict the search of the classes.

If iterated, the finder will yield a key/value tuple where the key is the fully-qualified class name
as string, and the value is a reflector object (could be a runtime `Reflector` or any other type of reflector object).

#### Examples

##### Finds all the classes into "src" folder

```php
use Kcs\ClassFinder\Finder\ComposerFinder;

$finder = new ComposerFinder();
$finder->path(__DIR__ . '/src');

foreach ($finder as $className => $reflector) {
    // Do magic things.
}
```

##### Finds all the classes implementing HttpClientInterface

```php
use Kcs\ClassFinder\Finder\ComposerFinder;
use Psr\Http\Client\HttpClientInterface;

$finder = new ComposerFinder();
$finder->implementationOf(HttpClientInterface::class);

foreach ($finder as $className => $reflector) {
    // All the yielded reflectors are referred to classes implementing of http client.
}
```

See [finder section](./finder.md) for more information

## License

The library is released under the business-friendly MIT license.  
This documentation is released under CC0 license.

## Contributing

Contributions are always welcome.  
Feel free to open a PR or file an issue.
