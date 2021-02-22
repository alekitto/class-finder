<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\Util;

use Kcs\ClassFinder\Util\Error;
use Kcs\ClassFinder\Util\ErrorHandler;
use PHPUnit\Framework\TestCase;
use Throwable;

use function call_user_func_array;
use function func_get_args;
use function restore_error_handler;
use function set_error_handler;
use function trigger_error;
use function unlink;

use const E_USER_ERROR;
use const E_USER_WARNING;

class ErrorHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        ErrorHandler::register();
    }

    protected function tearDown(): void
    {
        ErrorHandler::unregister();
    }

    public function testShouldPassNonErrorsToPreviousErrorHandler(): void
    {
        $this->expectWarning();
        trigger_error('This is a warning', E_USER_WARNING);
    }

    public function testShouldThrowErrorOnErrorOrUserError(): void
    {
        $this->expectException(Error::class);
        trigger_error('This is an error', E_USER_ERROR);
    }

    public function testShouldThrowErrorOnUnregisterIfErrorHandlerHasChanged(): void
    {
        set_error_handler(static fn () => false);

        $e = null;
        try {
            ErrorHandler::unregister();
        } catch (Throwable $e) {
        }

        self::assertNotNull($e);
        self::assertEquals('Error handler has changed, cannot unregister the handler', $e->getMessage());
        restore_error_handler();
    }

    public function testShouldPassErrorsToPreviousErrorHandlerIfSilenced(): void
    {
        $error = null;

        ErrorHandler::unregister();
        $previous = set_error_handler(static function () use (&$previous, &$error) {
            $error = func_get_args();

            return call_user_func_array($previous, $error);
        });

        ErrorHandler::register();
        @unlink('this_file_does_not_exist.bad_idea');

        self::assertNotNull($error);
        self::assertEquals('unlink(this_file_does_not_exist.bad_idea): No such file or directory', $error[1]);
    }
}
