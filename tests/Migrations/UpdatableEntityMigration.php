<?php

namespace Jinya\Database\Migrations;

use Jinya\Database\Migration\AbstractMigration;
use Jinya\Database\UpdatableEntityWithId;
use Jinya\Database\UpdatableEntityWithoutIdWithoutUniqueColumn;
use Jinya\Database\UpdatableEntityWithoutIdWithUniqueColumn;
use PDO;

use function Jinya\Database\get_identity;

class UpdatableEntityMigration extends AbstractMigration
{
    /**
     * @inheritDoc
     */
    public function up(PDO $pdo): void
    {
        $identity = get_identity();

        $tableName = UpdatableEntityWithId::getTableName();
        $pdo->exec(
            "
        create table $tableName (
            id integer primary key $identity,
            name varchar(255) not null,
            display_name varchar(255) not null,
            date timestamp not null
        )"
        );

        $tableName = UpdatableEntityWithoutIdWithUniqueColumn::getTableName();
        $pdo->exec(
            "
        create table $tableName (
            name varchar(255) not null,
            display_name varchar(255) not null,
            date timestamp not null
        )"
        );

        $tableName = UpdatableEntityWithoutIdWithoutUniqueColumn::getTableName();
        $pdo->exec(
            "
        create table $tableName (
            name varchar(255) not null,
            display_name varchar(255) not null,
            date timestamp not null
        )"
        );
    }

    /**
     * @inheritDoc
     */
    public function down(PDO $pdo): void
    {
        $tableName = UpdatableEntityWithId::getTableName();
        $pdo->exec("drop table $tableName");

        $tableName = UpdatableEntityWithoutIdWithUniqueColumn::getTableName();
        $pdo->exec("drop table $tableName");

        $tableName = UpdatableEntityWithoutIdWithoutUniqueColumn::getTableName();
        $pdo->exec("drop table $tableName");
    }
}
