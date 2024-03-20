--TEST--
ErrorHandler - Test User Notice does not throw Error
--FILE--
<?php

use Kcs\ClassFinder\Util\ErrorHandler;

require __DIR__ . '/../../../vendor/autoload.php';

ErrorHandler::register();
trigger_error('This is a notice', E_USER_NOTICE);

?>
--EXPECTF--
Notice: This is a notice in %a
