<?php

namespace Jinya\Database;

use Attribute;
use DateTime;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DateConverter implements ValueConverter
{
    public function __construct(public readonly string $format)
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
