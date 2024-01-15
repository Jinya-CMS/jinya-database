<?php

namespace Jinya\Database\Migrations;

use Jinya\Database\DeletableEntityWithId;
use Jinya\Database\DeletableEntityWithoutIdWithoutUniqueColumn;
use Jinya\Database\DeletableEntityWithoutIdWithUniqueColumn;
use Jinya\Database\Migration\AbstractMigration;
use PDO;

use function Jinya\Database\get_identity;

class DeletableEntityMigration extends AbstractMigration
{
    /**
     * @inheritDoc
     */
    public function up(PDO $pdo): void
    {
        $identity = get_identity();

        $tableName = DeletableEntityWithId::getTableName();
        $pdo->exec(
            "
        create table $tableName (
            id integer primary key $identity,
            name varchar(255) not null,
            display_name varchar(255) not null,
            date timestamp not null
        )"
        );

        $tableName = DeletableEntityWithoutIdWithUniqueColumn::getTableName();
        $pdo->exec(
            "
        create table $tableName (
            name varchar(255) not null,
            display_name varchar(255) not null,
            date timestamp not null
        )"
        );

        $tableName = DeletableEntityWithoutIdWithoutUniqueColumn::getTableName();
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
        $tableName = DeletableEntityWithId::getTableName();
        $pdo->exec("drop table $tableName");

        $tableName = DeletableEntityWithoutIdWithUniqueColumn::getTableName();
        $pdo->exec("drop table $tableName");

        $tableName = DeletableEntityWithoutIdWithoutUniqueColumn::getTableName();
        $pdo->exec("drop table $tableName");
    }
}
