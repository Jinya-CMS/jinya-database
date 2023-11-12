<?php

namespace Jinya\Database\Cache;

use Jinya\Database\ValueConverter;

class CacheColumn
{
    public function __construct(
        public readonly string $propertyName = '',
        public readonly string $sqlName = '',
        public readonly ValueConverter|null $converter = null
    ) {
    }
}
