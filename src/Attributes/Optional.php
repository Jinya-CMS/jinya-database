<?php

namespace Jinya\Database\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Optional
{
    public function __construct(
        public readonly mixed $defaultValue = null,
    ) {
    }
}
