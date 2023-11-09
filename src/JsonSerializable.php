<?php

namespace Jinya\Database;

interface JsonSerializable extends \JsonSerializable
{
    /**
     * Deserializes the given JSON array into this type
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function jsonDeserialize(array $data): mixed;
}
