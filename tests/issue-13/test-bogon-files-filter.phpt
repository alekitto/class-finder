--TEST--
ComposerFinder - compatibility with symfony/cache: exclude path (#13)
--FILE--
<?php
require __DIR__ . '/vendor/autoload.php';

$finder = (new Kcs\ClassFinder\Finder\ComposerFinder())
    ->skipBogonFiles();

$count = 0;
foreach ($finder as $className => $reflector) {
    ++$count;
}

echo "OK"
?>
--EXPECT--
OK
