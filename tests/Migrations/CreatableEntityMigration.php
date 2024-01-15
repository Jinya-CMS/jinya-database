<?php

namespace Jinya\Database\Migrations;

use Jinya\Database\CreatableEntity;
use Jinya\Database\Migration\AbstractMigration;
use PDO;

use function Jinya\Database\get_identity;

class CreatableEntityMigration extends AbstractMigration
{
    /**
     * @inheritDoc
     */
    public function up(PDO $pdo): void
    {
        $identity = get_identity();

        $tableName = CreatableEntity::getTableName();
        $pdo->exec(
            "
        create table $tableName (
            id integer primary key $identity,
            name varchar(255) not null,
            display_name varchar(255),
            date timestamp
        )"
        );
    }

    /**
     * @inheritDoc
     */
    public function down(PDO $pdo): void
    {
        $tableName = CreatableEntity::getTableName();
        $pdo->exec("drop table $tableName");
    }
}
