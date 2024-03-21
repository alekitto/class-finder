--TEST--
ComposerFinder - should list all classes in a project w/o autoloading
--FILE--
<?php
require __DIR__ . '/../../vendor/autoload.php';

$finder = (new Kcs\ClassFinder\Finder\ComposerFinder())->useAutoloading(false);
iterator_to_array($finder);

echo "OK";
?>
--EXPECT--
OK
