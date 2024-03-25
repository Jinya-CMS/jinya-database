<?php

namespace Jinya\Database\Converters;

use Attribute;
use DateTime;
use Jinya\Database\ValueConverter;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class NullableDateConverter implements ValueConverter
{
    public function __construct(public string $format)
    {
    }

    /**
     * @inheritDoc
     * @param string $input
     * @return DateTime|null
     */
    public function from(mixed $input): DateTime|null
    {
        return DateTime::createFromFormat($this->format, $input) ?: null;
    }

    /**
     * @inheritDoc
     * @param DateTime|null $input
     * @return string|null
     */
    public function to(mixed $input): string|null
    {
        return $input?->format($this->format);
    }
}
