<?php

namespace Jinya\Database;

use PHPUnit\Framework\TestCase;

class DeletableEntityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $identity = match (getenv('DATABASE_TYPE')) {
            'mysql' => 'auto_increment',
            'sqlite' => 'autoincrement',
            'pgsql' => 'generated always as identity',
            default => throw new \RuntimeException(),
        };

        $tableName = DeletableEntityWithId::getTableName();
        DeletableEntityWithId::getPDO()->exec(
            "
        create table $tableName (
            id integer primary key $identity,
            name varchar(255) not null,
            display_name varchar(255) not null,
            date timestamp not null
        )"
        );

        $tableName = DeletableEntityWithoutIdWithUniqueColumn::getTableName();
        DeletableEntityWithoutIdWithUniqueColumn::getPDO()->exec(
            "
        create table $tableName (
            name varchar(255) not null,
            display_name varchar(255) not null,
            date timestamp not null
        )"
        );

        $tableName = DeletableEntityWithoutIdWithoutUniqueColumn::getTableName();
        DeletableEntityWithoutIdWithoutUniqueColumn::getPDO()->exec(
            "
        create table $tableName (
            name varchar(255) not null,
            display_name varchar(255) not null,
            date timestamp not null
        )"
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $tableName = DeletableEntityWithId::getTableName();
        DeletableEntityWithId::getPDO()->exec("drop table $tableName");

        $tableName = DeletableEntityWithoutIdWithUniqueColumn::getTableName();
        DeletableEntityWithoutIdWithUniqueColumn::getPDO()->exec("drop table $tableName");

        $tableName = DeletableEntityWithoutIdWithoutUniqueColumn::getTableName();
        DeletableEntityWithoutIdWithoutUniqueColumn::getPDO()->exec("drop table $tableName");
    }

    public function testDeleteWithId(): void
    {
        $row = ['name' => 'Test', 'display_name' => 'Test case', 'date' => '2022-09-11 20:34:25'];
        $statement = DeletableEntityWithId::getQueryBuilder()
            ->newInsert()
            ->into(DeletableEntityWithId::getTableName())
            ->addRow($row);
        DeletableEntityWithId::getPDO()->prepare($statement->getStatement())->execute($statement->getBindValues());

        $entity = DeletableEntityWithId::findById(1);
        $entity->delete();

        self::assertNull(DeletableEntityWithId::findById(1));
    }

    public function testDeleteWithoutIdWithUniqueColumn(): void
    {
        $rows = [
            ['name' => 'Test', 'display_name' => 'Test case', 'date' => '2022-09-11 20:34:25'],
            ['name' => 'Test 2', 'display_name' => 'Test case', 'date' => '2022-09-11 20:34:25'],
        ];
        $statement = DeletableEntityWithoutIdWithUniqueColumn::getQueryBuilder()
            ->newInsert()
            ->into(DeletableEntityWithoutIdWithUniqueColumn::getTableName())
            ->addRows($rows);
        DeletableEntityWithoutIdWithUniqueColumn::getPDO()->prepare($statement->getStatement())->execute(
            $statement->getBindValues()
        );

        $entity = DeletableEntityWithoutIdWithUniqueColumn::findByFilters(
            ['name = ?' => ['Test']],
            'name ASC'
        )->current();
        $entity->delete();

        self::assertCount(1, iterator_to_array(DeletableEntityWithoutIdWithUniqueColumn::findAll('name ASC')));
    }

    public function testDeleteWithoutIdWithoutUniqueColumn(): void
    {
        $rows = [
            ['name' => 'Test', 'display_name' => 'Test case', 'date' => '2022-09-11 20:34:25'],
            ['name' => 'Test 2', 'display_name' => 'Test case', 'date' => '2022-09-11 20:34:25'],
        ];
        $statement = DeletableEntityWithoutIdWithoutUniqueColumn::getQueryBuilder()
            ->newInsert()
            ->into(DeletableEntityWithoutIdWithoutUniqueColumn::getTableName())
            ->addRows($rows);
        DeletableEntityWithoutIdWithoutUniqueColumn::getPDO()->prepare($statement->getStatement())->execute(
            $statement->getBindValues()
        );

        $entity = DeletableEntityWithoutIdWithoutUniqueColumn::findByFilters(
            ['name = ?' => ['Test']],
            'name ASC'
        )->current();
        $entity->delete();

        self::assertCount(1, iterator_to_array(DeletableEntityWithoutIdWithoutUniqueColumn::findAll('name ASC')));
    }
}
