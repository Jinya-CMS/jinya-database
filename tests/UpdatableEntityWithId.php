<?php

namespace Jinya\Database;

use DateTime;
use Jinya\Database\Attributes\Column;
use Jinya\Database\Attributes\Id;
use Jinya\Database\Attributes\Table;
use Jinya\Database\Converters\DateConverter;

#[Table('updatable_entity')]
class UpdatableEntityWithId extends Entity
{
    #[Id]
    #[Column(autogenerated: true)]
    public int $id;

    #[Column]
    public string $name;

    #[Column(sqlName: 'display_name')]
    public string $displayName;

    #[Column]
    #[DateConverter('Y-m-d H:i:s')]
    public DateTime $date;
}
