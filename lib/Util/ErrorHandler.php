<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Util;

use Error;

use function error_reporting;
use function restore_error_handler;
use function set_error_handler;

use const E_DEPRECATED;
use const E_NOTICE;
use const E_STRICT;
use const E_USER_DEPRECATED;
use const E_USER_NOTICE;
use const E_USER_WARNING;
use const E_WARNING;

/**
 * @internal
 */
final class ErrorHandler
{
    private static bool $registered = false;

    public static function handleError(int $errorNumber, string $errorString): bool
    {
        // Do not raise an exception when the error suppression operator (@) was used.
        if (! ($errorNumber & error_reporting())) {
            return false;
        }

        switch ($errorNumber) {
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_WARNING:
            case E_USER_WARNING:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
            case E_STRICT:
                break;

            default:
                throw new Error($errorString, $errorNumber);
        }

        return false;
    }

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        $oldErrorHandler = set_error_handler([self::class, 'handleError']);

        if ($oldErrorHandler !== null) {
            restore_error_handler();

            return;
        }

        self::$registered = true;
    }

    public static function unregister(): void
    {
        if (! self::$registered) {
            return;
        }

        restore_error_handler();
    }
}
