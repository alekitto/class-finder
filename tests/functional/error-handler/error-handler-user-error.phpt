--TEST--
ErrorHandler - Test User Error does throw Error
--FILE--
<?php

use Kcs\ClassFinder\Util\ErrorHandler;

require __DIR__ . '/../../../vendor/autoload.php';

ErrorHandler::register();
trigger_error('This is an error', E_USER_ERROR);

?>
--EXPECTF--
Fatal error: Uncaught Kcs\ClassFinder\Util\Error: This is an error in %a
