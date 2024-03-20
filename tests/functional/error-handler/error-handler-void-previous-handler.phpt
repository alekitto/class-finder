--TEST--
ErrorHandler - Test User Notice does not panic if previous handler returns void
--FILE--
<?php

use Kcs\ClassFinder\Util\ErrorHandler;

require __DIR__ . '/../../../vendor/autoload.php';

set_error_handler(static function (): void {});

ErrorHandler::register();
trigger_error('This is a notice', E_USER_NOTICE);

?>
--EXPECTF--
Notice: This is a notice in %a
