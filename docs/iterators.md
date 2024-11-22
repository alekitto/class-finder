# Iterators

The finders listed in [finder section](./finder.md?id=finder) use underlying iterator classes to find PHP files and do some *quick and dirty* filtering.

While iterators should not be used directly (finders should be used instead), they can be used to create new (possibly more specific) finders.

## Available iterators

- `ClassIterator` - the base iterator class for all the iterators; implements all the basic operations.
- `ComposerIterator` - this iterator uses the composer loader and yields *all* the classes in the classmap and in the psr-0/psr-4 prefixes. If composer created an authoritative classmap, the psr prefixes will be not iterated.
- `FilteredComposerIterator` - the same of `ComposerIterator`, but does some *quick* filtering on namespaces and directories. These filters are not precise enough and its results need to be re-processed.
- `Psr0Iterator`/`Psr4Iterator` - explore a psr prefix searching for classes.
- `RecursiveIterator` - uses a `RecursiveIteratorIterator` to search for PHP files
- `PhpDocumentorIterator` - analyzes a directory with php documentor and collects the found classes.
- `PhpParserIterator` - recursively scan a directory files with php-parser and iterates on the found symbols.
- `ClassMapIterator` - accepts a class-map array, including the listed files and collecting the declared classes.
