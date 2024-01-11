<?php

namespace Jinya\Database\Converters;

use Attribute;
use DateTime;
use Jinya\Database\ValueConverter;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class DateConverter implements ValueConverter
{
    public function __construct(public string $format)
    {
    }

    /**
     * @inheritDoc
     * @param string $input
     * @return DateTime
     */
    public function from(mixed $input): mixed
    {
        return DateTime::createFromFormat($this->format, $input) ?: new DateTime();
    }

    /**
     * @inheritDoc
     * @param DateTime $input
     * @return string
     */
    public function to(mixed $input): mixed
    {
        return $input->format($this->format);
    }
}
