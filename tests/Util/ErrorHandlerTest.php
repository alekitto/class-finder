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
        set_error_handler(function (int $errno, string $errstr) {
            throw new \Error($errstr, $errno);
        }, E_USER_WARNING);

        try {
            trigger_error('This is a warning', E_USER_WARNING);
        } catch (\Error $e) {
            self::assertEquals('This is a warning', $e->getMessage());
            self::assertEquals(E_USER_WARNING, $e->getCode());
        } finally {
            restore_error_handler();
        }
    }

    public function testShouldThrowErrorOnErrorOrUserError(): void
    {
        $this->expectException(Error::class);
        trigger_error('This is an error', E_USER_ERROR);
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

    public function testShouldNotCrashIfPreviousErrorHandlerReturnsNullOrVoid(): void
    {
        $this->expectNotToPerformAssertions();

        ErrorHandler::unregister();
        $previous = set_error_handler(static function () use (&$previous): void {
            call_user_func_array($previous, func_get_args());
        });

        ErrorHandler::register();
        @unlink('this_file_does_not_exist.bad_idea');
    }
}
