<?php

namespace Jinya\Database\Attributes;

use Attribute;
use Jinya\Database\ValueConverter;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    public function __construct(
        public readonly string $sqlName = '',
        public readonly ValueConverter|null $converter = null
    ) {
    }
}
