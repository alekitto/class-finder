<?php

declare(strict_types=1);

set_error_handler(static function (int $errno, string $errstr) {
    return false;
}, E_ALL ^ ~E_USER_DEPRECATED);
