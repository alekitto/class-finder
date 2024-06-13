<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Util;

use Closure;

use function preg_match;

final class BogonFilesFilter
{
    private const BOGON_FILES_REGEX = '#' .
        // https://github.com/alekitto/class-finder/issues/13#issuecomment-2010509501
        '(?:' .
            '(?:symfony/(?:cache|symfony/Component/Cache)/Traits/(?:Redis(?:Cluster)?\dProxy|ValueWrapper)|' .
            'php-coveralls/php-coveralls/src/Bundle/CoverallsBundle/Console/Application|' .
            'dealerdirect/phpcodesniffer-composer-installer/src/Plugin|' .
            'myclabs/php-enum/src/PHPUnit/Comparator|' .
            'guzzlehttp/guzzle/src/functions)' .
        '\.php$)' .
    '#x';

    public static function getFileFilterFn(Closure|null $filter = null): Closure
    {
        $filter ??= static fn () => true;

        return static function (string $path) use ($filter): bool {
            if (preg_match(self::BOGON_FILES_REGEX, $path) === 1) {
                return false;
            }

            return $filter($path);
        };
    }
}
