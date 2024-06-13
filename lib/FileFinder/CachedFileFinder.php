<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FileFinder;

use Psr\Cache\CacheItemPoolInterface;
use SplFileInfo;

use function hash;

final class CachedFileFinder implements FileFinderInterface
{
    public function __construct(
        private readonly FileFinderInterface $innerFinder,
        private readonly CacheItemPoolInterface $cacheItemPool,
    ) {
    }

    /** @inheritDoc */
    public function search(string $pattern): iterable
    {
        $cacheKey = hash('sha256', $pattern);
        $item = $this->cacheItemPool->getItem($cacheKey);
        if ($item->isHit()) {
            $files = $item->get();
        } else {
            $files = [];
            foreach ($this->innerFinder->search($pattern) as $path => $_) { // phpcs:ignore Squiz.NamingConventions.ValidVariableName.NotCamelCaps
                $files[] = $path;
            }

            $item->set($files);
            $this->cacheItemPool->save($item);
        }

        foreach ($files as $file) {
            yield $file => new SplFileInfo($file);
        }
    }
}
