--TEST--
ComposerFinder - nested composer projects
--FILE--
<?php
require_once 'vendor/autoload.php';
$otherProjectClassLoader = require __DIR__ . '/otherProject/vendor/autoload.php';

$finder = new Kcs\ClassFinder\Finder\ComposerFinder($otherProjectClassLoader);
iterator_to_array($finder);

echo "OK";
?>
--EXPECT--
OK
