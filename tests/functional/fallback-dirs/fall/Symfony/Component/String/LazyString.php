<?php

declare(strict_types=1);

namespace Symfony\Component\String;

class LazyString implements \Stringable, \JsonSerializable
{

    public function __toString(): string
    {
        return 'test';
    }

    public function jsonSerialize(): mixed
    {
        return [];
    }
}
