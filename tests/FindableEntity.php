<?php

namespace Jinya\Database;

use DateTime;
use Jinya\Database\Attributes\Column;
use Jinya\Database\Attributes\Table;

#[Table('just-a-test')]
class FindableEntity implements Findable, JsonSerializable
{
    use FindableEntityTrait;

    #[Column]
    public int $id;

    #[Column]
    public string $name;

    #[Column(sqlName: 'display_name')]
    public string $displayName;

    #[Column(converter: new DateConverter('Y-m-d H:i:s'))]
    public DateTime $date;
}
