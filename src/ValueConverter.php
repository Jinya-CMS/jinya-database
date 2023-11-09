<?php

namespace Jinya\Database;

interface ValueConverter
{
    /**
     * Converts the given value to the new value
     *
     * @param mixed $input
     * @return mixed
     */
    public function from(mixed $input): mixed;

    /**
     * Converts the given value to the new value
     *
     * @param mixed $input
     * @return mixed
     */
    public function to(mixed $input): mixed;
}
