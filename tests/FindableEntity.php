<?php

namespace Jinya\Database;

use DateTime;
use Jinya\Database\Attributes\Column;
use Jinya\Database\Attributes\Table;

#[Table('findable_entity')]
class FindableEntity implements Findable
{
    use FindableEntityTrait;

    #[Column]
    public int $id;

    #[Column]
    public string $name;

    #[Column(sqlName: 'display_name')]
    public string $displayName;

    #[Column]
    #[DateConverter('Y-m-d H:i:s')]
    public DateTime $date;
}
