<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\Reflection;

use Iterator;
use Kcs\ClassFinder\FilterIterator\MultiplePcreFilterIterator;
use ReflectionClass;

use function preg_quote;
use function str_replace;

use const DIRECTORY_SEPARATOR;

/**
 * @template-covariant TValue of ReflectionClass
 * @template T of Iterator<class-string, TValue>
 * @template-extends MultiplePcreFilterIterator<TValue, T>
 */
final class PathFilterIterator extends MultiplePcreFilterIterator
{
    /**
     * Filters the iterator values.
     *
     * @return bool true if the value should be kept, false otherwise
     */
    public function accept(): bool
    {
        $reflector = $this->getInnerIterator()->current();
        if ($reflector->isInternal()) {
            return false;
        }

        $filename = $reflector->getFileName();
        if ($filename === false) {
            return false;
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            $filename = str_replace('\\', '/', $filename);
        }

        return $this->isAccepted($filename);
    }

    /**
     * Converts strings to regexp.
     *
     * PCRE patterns are left unchanged.
     *
     * Default conversion:
     *     'lorem/ipsum/dolor' ==>  'lorem\/ipsum\/dolor/'
     *
     * Use only / as directory separator (on Windows also).
     *
     * @param string $str Pattern: regexp or dirname
     *
     * @return string regexp corresponding to a given string or regexp
     */
    public static function toRegex(string $str): string
    {
        return self::isRegex($str) ? $str : '/' . preg_quote($str, '/') . '/';
    }
}
