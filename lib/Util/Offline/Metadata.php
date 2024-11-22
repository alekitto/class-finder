<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\Util\Offline;

use phpDocumentor\Reflection\Metadata\Metadata as PhpDocumentorMetadata;

class Metadata implements PhpDocumentorMetadata
{
    public const METADATA_KEY = self::class;

    /** @param object[] $superclasses */
    public function __construct(
        public array $superclasses,
    ) {
    }

    public function key(): string
    {
        return self::METADATA_KEY;
    }
}
