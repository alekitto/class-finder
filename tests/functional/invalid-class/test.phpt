--TEST--
ComposerFinder - nested composer projects
--FILE--
<?php
require_once 'vendor/autoload.php';

$finder = (new Kcs\ClassFinder\Finder\PhpDocumentorFinder(__DIR__))
    ->inNamespace('Kcs\ClassFinder\FunctionalTests');
var_dump(array_keys(iterator_to_array($finder)));

$finder = (new Kcs\ClassFinder\Finder\PhpParserFinder(__DIR__))
    ->inNamespace('Kcs\ClassFinder\FunctionalTests');
var_dump(array_keys(iterator_to_array($finder)));

echo "OK";
?>
--EXPECT--
array(1) {
  [0]=>
  string(38) "Kcs\ClassFinder\FunctionalTests\Foobar"
}
array(1) {
  [0]=>
  string(38) "Kcs\ClassFinder\FunctionalTests\Foobar"
}
OK
