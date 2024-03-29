--TEST--
ComposerFinder - compatibility with symfony/cache: exclude namespace (#13)
--FILE--
<?php
require __DIR__ . '/vendor/autoload.php';

$finder = (new Kcs\ClassFinder\Finder\ComposerFinder())
    ->notInNamespace('Symfony\Component\Cache\Traits');

$count = 0;
foreach ($finder as $className => $reflector) {
    ++$count;
}

echo "OK"
?>
--EXPECT--
OK
