<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\Util;

use Kcs\ClassFinder\Util\Error;
use Kcs\ClassFinder\Util\ErrorHandler;
use PHPUnit\Framework\TestCase;

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
}
