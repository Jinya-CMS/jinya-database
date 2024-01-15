<?php

namespace Jinya\Database\Migrations;

use Jinya\Database\Migration\AbstractMigration;
use Jinya\Database\TestEntity;
use PDO;

use function Jinya\Database\get_identity;

class EntityMigration extends AbstractMigration
{
    public function up(PDO $pdo): void
    {
        $identity = get_identity();

        $tableName = TestEntity::getTableName();
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
        for ($i = 10; $i < 21; ++$i) {
            $rows[] = ['name' => "Test $i", 'display_name' => "Test case $i", 'date' => "20$i-09-11 20:34:25"];
        }

        $statement = TestEntity::getQueryBuilder()
            ->newInsert()
            ->into(TestEntity::getTableName())
            ->addRows($rows);
        $pdo->prepare($statement->getStatement())->execute($statement->getBindValues());
    }

    public function down(PDO $pdo): void
    {
        $tableName = TestEntity::getTableName();
        $pdo->exec("drop table $tableName");
    }
}
