--TEST--
ErrorHandler - Test User Warning does not throw Error
--FILE--
<?php

use Kcs\ClassFinder\Util\ErrorHandler;

require __DIR__ . '/../../../vendor/autoload.php';

ErrorHandler::register();
trigger_error('This is a warning', E_USER_WARNING);

?>
--EXPECTF--
Warning: This is a warning in %a
