<?php

namespace Jinya\Database;

use DateTime;
use Jinya\Database\Attributes\Column;
use Jinya\Database\Attributes\Table;
use Jinya\Database\Converters\DateConverter;

#[Table('updatable_entity_without_id_with_unique_column')]
class UpdatableEntityWithoutIdWithUniqueColumn implements Findable, Updatable
{
    use FindableEntityTrait;
    use UpdatableEntityTrait;

    #[Column(unique: true)]
    public string $name;

    #[Column(sqlName: 'display_name')]
    public string $displayName;

    #[Column]
    #[DateConverter('Y-m-d H:i:s')]
    public DateTime $date;
}
