<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Util\PhpDocumentor;

use PhpDocumentor\Reflection\Metadata\Metadata;
use WeakMap;

final class MetadataRegistry
{
    /** @var WeakMap<object, array<string, Metadata>> */
    private WeakMap $map;

    public function __construct()
    {
        $this->map = new WeakMap();
    }

    public function addMetadata(object $target, Metadata $metadata): void
    {
        $storage = $this->map[$target] ?? [];
        $storage[$metadata->key()] = $metadata;

        $this->map[$target] = $storage;
    }

    /** @return array<string, Metadata> */
    public function getMetadata(object $target): array
    {
        return $this->map[$target] ?? [];
    }

    public static function getInstance(): self
    {
        static $instance;
        if ($instance === null) {
            $instance = new self();
        }

        return $instance;
    }
}
