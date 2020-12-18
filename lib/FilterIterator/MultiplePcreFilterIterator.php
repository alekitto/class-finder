<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator;

use FilterIterator;
use Iterator;

use function Safe\preg_match;
use function Safe\substr;

abstract class MultiplePcreFilterIterator extends FilterIterator
{
    /** @var string[] */
    protected array $matchRegexps = [];

    /** @var string[] */
    protected array $noMatchRegexps = [];

    /**
     * @param Iterator<mixed> $iterator The Iterator to filter
     * @param string[]     $matchPatterns   An array of patterns that need to match
     * @param string[]     $noMatchPatterns An array of patterns that need to not match
     */
    public function __construct(Iterator $iterator, array $matchPatterns, array $noMatchPatterns)
    {
        foreach ($matchPatterns as $pattern) {
            $this->matchRegexps[] = $this->toRegex($pattern);
        }

        foreach ($noMatchPatterns as $pattern) {
            $this->noMatchRegexps[] = $this->toRegex($pattern);
        }

        parent::__construct($iterator);
    }

    /**
     * Checks whether the string is a regex.
     *
     * @return bool Whether the given string is a regex
     */
    public static function isRegex(string $str): bool
    {
        if (preg_match('/^(.{3,}?)[imsxuADU]*$/', $str, $m)) {
            $start = substr($m[1], 0, 1);
            $end = substr($m[1], -1);

            if ($start === $end) {
                return ! preg_match('/[*?[:alnum:] \\\\]/', $start);
            }

            foreach ([['{', '}'], ['(', ')'], ['[', ']'], ['<', '>']] as $delimiters) {
                if ($start === $delimiters[0] && $end === $delimiters[1]) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks whether the string is accepted by the regex filters.
     *
     * If there is no regexps defined in the class, this method will accept the string.
     * Such case can be handled by child classes before calling the method if they want to
     * apply a different behavior.
     *
     * @param string $string The string to be matched against filters
     */
    protected function isAccepted(string $string): bool
    {
        // should at least not match one rule to exclude
        foreach ($this->noMatchRegexps as $regex) {
            if (preg_match($regex, $string)) {
                return false;
            }
        }

        // should at least match one rule
        if ($this->matchRegexps) {
            foreach ($this->matchRegexps as $regex) {
                if (preg_match($regex, $string)) {
                    return true;
                }
            }

            return false;
        }

        // If there is no match rules, the file is accepted
        return true;
    }

    /**
     * Converts string into regexp.
     *
     * @param string $str Pattern
     *
     * @return string regexp corresponding to a given string
     */
    abstract public static function toRegex(string $str): string;
}
