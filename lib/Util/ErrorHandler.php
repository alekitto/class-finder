<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Util;

use function call_user_func_array;
use function error_reporting;
use function func_get_args;
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

    /** @var callable */
    private static $previous;

    public static function handleError(int $errorNumber, string $errorString): bool
    {
        // Do not raise an exception when the error suppression operator (@) was used.
        if (! ($errorNumber & error_reporting())) {
            return call_user_func_array(self::$previous, func_get_args());
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

        return call_user_func_array(self::$previous, func_get_args());
    }

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        self::$previous = set_error_handler([self::class, 'handleError']) ?? static fn () => false;
        self::$registered = true;
    }

    public static function unregister(): void
    {
        if (! self::$registered) {
            return;
        }

        $previous = set_error_handler(static fn () => false);
        restore_error_handler();
        if ($previous !== [self::class, 'handleError']) {
            throw new Error('Error handler has changed, cannot unregister the handler');
        }

        restore_error_handler();
        self::$registered = false;
    }
}
