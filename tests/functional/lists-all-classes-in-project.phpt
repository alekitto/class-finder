--TEST--
ComposerFinder - should list all classes in a project w/o autoloading
--FILE--
<?php
require __DIR__ . '/../../vendor/autoload.php';

$finder = (new Kcs\ClassFinder\Finder\ComposerFinder())->useAutoloading(false);
$classes = iterator_to_array($finder);

printf('> found %d class(es)' . PHP_EOL, count($classes));
?>
--EXPECTF--
> found %d class(es)
