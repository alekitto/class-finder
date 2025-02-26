<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\Metadata;

use function interface_exists;

if (! interface_exists(Metadata::class)) {
    interface Metadata
    {
        public function key(): string;
    }
}
