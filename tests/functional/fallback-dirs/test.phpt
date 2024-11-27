--TEST--
Fallback directories
--INI--
error_reporting=E_ALL & ~E_NOTICE & ~E_DEPRECATED
--FILE--
<?php
require_once __DIR__ . '/vendor/autoload.php';

$finder = (new Kcs\ClassFinder\Finder\ComposerFinder())->skipBogonFiles();
$arr = iterator_to_array($finder);

/** @var ReflectionClass $class */
$class = $arr[\Symfony\Component\String\LazyString::class];
echo str_replace(DIRECTORY_SEPARATOR, '/', $class->getFileName());
?>
--EXPECTF--
%s/tests/functional/fallback-dirs/vendor/symfony/string/LazyString.php
