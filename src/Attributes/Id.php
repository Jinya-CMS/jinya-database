<?php

namespace Jinya\Database\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Id
{
    public function __construct()
    {
    }
}
