<?php

namespace Jinya\Database\Migrations;

use Jinya\Database\FindableEntity;
use Jinya\Database\Migration\AbstractMigration;
use PDO;

use function Jinya\Database\get_identity;

class FindableEntityMigration extends AbstractMigration
{
    /**
     * @inheritDoc
     */
    public function up(PDO $pdo): void
    {
        $identity = get_identity();

        $tableName = FindableEntity::getTableName();
        $pdo->exec(
            "
        create table $tableName (
            id integer primary key $identity,
            name varchar(255) not null,
            display_name varchar(255) not null,
            date timestamp not null
        )"
        );

        $rows = [];
        for ($i = 11; $i < 21; ++$i) {
            $rows[] = ['name' => "Test $i", 'display_name' => "Test case $i", 'date' => "20$i-09-11 20:34:25"];
        }

        $statement = FindableEntity::getQueryBuilder()
            ->newInsert()
            ->into(FindableEntity::getTableName())
            ->addRows($rows);
        $pdo->prepare($statement->getStatement())->execute($statement->getBindValues());
    }

    /**
     * @inheritDoc
     */
    public function down(PDO $pdo): void
    {
        $tableName = FindableEntity::getTableName();
        $pdo->exec("drop table $tableName");
    }
}
