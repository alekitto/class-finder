--TEST--
ComposerFinder - compatibility with symfony/cache: exclude path (#13)
--FILE--
<?php
require __DIR__ . '/vendor/autoload.php';

$finder = (new Kcs\ClassFinder\Finder\ComposerFinder())
    ->pathFilter(static fn (string $path): bool => !preg_match('#symfony/cache/Traits/Redis(?:Cluster)?\dProxy\.php$#', $path));

$count = 0;
foreach ($finder as $className => $reflector) {
    ++$count;
}
printf('> found %d class(es)' . PHP_EOL, $count);
?>
--EXPECTF--
> found %d class(es)
