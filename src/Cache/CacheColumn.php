<?php

namespace Jinya\Database\Cache;

use Jinya\Database\Attributes\Column;
use Jinya\Database\ValueConverter;

class CacheColumn extends Column
{
    public function __construct(
        public readonly string $propertyName = '',
        public readonly string $sqlName = '',
        public readonly ValueConverter|null $converter = null
    ) {
        parent::__construct($this->sqlName, $this->converter);
    }
}
