<?php

namespace Jinya\Database\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    public function __construct(
        public readonly string $sqlName = ''
    ) {
    }
}
