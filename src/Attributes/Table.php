<?php

namespace Jinya\Database\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Table
{
    public function __construct(public readonly string $name)
    {
    }
}
