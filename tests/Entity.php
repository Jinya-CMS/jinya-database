<?php

namespace Jinya\Database;

use DateTime;
use Jinya\Database\Attributes\Column;
use Jinya\Database\Attributes\Table;

#[Table('entity')]
class Entity implements JsonSerializable
{
    use EntityTrait;

    #[Column]
    public int $id;

    #[Column]
    public string $name;

    #[Column(sqlName: 'display_name')]
    public string $displayName;

    #[Column(converter: new DateConverter('Y-m-d H:i:s'))]
    public DateTime $date;
}