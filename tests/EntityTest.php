<?php

namespace Jinya\Database;

use PHPUnit\Framework\TestCase;

class EntityTest extends TestCase
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

        $tableName = Entity::getTableName();
        Entity::getPDO()->exec(
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

        $statement = Entity::getQueryBuilder()
            ->newInsert()
            ->into(Entity::getTableName())
            ->addRows($rows);
        Entity::getPDO()->prepare($statement->getStatement())->execute($statement->getBindValues());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $tableName = Entity::getTableName();
        Entity::getPDO()->exec("drop table $tableName");
    }

    public function testGetPDO(): void
    {
        $pdo = Entity::getPDO();

        self::assertNotNull($pdo);

        $pdo2 = Entity::getPDO();

        self::assertEquals($pdo, $pdo2);
    }

    public function testExecuteQuery(): void
    {
        $queryBuilder = Entity::getQueryBuilder();
        $query = $queryBuilder
            ->newInsert()
            ->into(Entity::getTableName())
            ->addRow(['name' => 'Test 1', 'display_name' => 'Test case 1', 'date' => '2020-09-11 20:34:25']);
        $id = Entity::executeQuery($query);
        self::assertIsString($id);

        $query = $queryBuilder
            ->newSelect()
            ->from(Entity::getTableName())
            ->cols(['*'])
            ->where('id = :id', ['id' => (int)$id]);
        /** @var array<array<string, mixed>> $result */
        $result = Entity::executeQuery($query);
        self::assertNotEmpty($result);
        $entry = $result[0];
        self::assertArrayHasKey('id', $entry);
        self::assertArrayHasKey('name', $entry);
        self::assertEquals('Test 1', $entry['name']);
        self::assertArrayHasKey('display_name', $entry);
        self::assertEquals('Test case 1', $entry['display_name']);
        self::assertArrayHasKey('date', $entry);
        self::assertEquals('2020-09-11 20:34:25', $entry['date']);

        $query = $queryBuilder
            ->newUpdate()
            ->table(Entity::getTableName())
            ->cols(['display_name' => 'Test case 4'])
            ->where('id = :id', ['id' => (int)$id]);
        $result = Entity::executeQuery($query);
        self::assertTrue($result);

        $query = $queryBuilder
            ->newSelect()
            ->from(Entity::getTableName())
            ->cols(['*'])
            ->where('id = :id', ['id' => (int)$id]);
        /** @var array<array<string, mixed>> $result */
        $result = Entity::executeQuery($query);
        self::assertNotEmpty($result);
        $entry = $result[0];
        self::assertArrayHasKey('id', $entry);
        self::assertArrayHasKey('name', $entry);
        self::assertEquals('Test 1', $entry['name']);
        self::assertArrayHasKey('display_name', $entry);
        self::assertEquals('Test case 4', $entry['display_name']);
        self::assertArrayHasKey('date', $entry);
        self::assertEquals('2020-09-11 20:34:25', $entry['date']);

        $query = $queryBuilder
            ->newDelete()
            ->from(Entity::getTableName())
            ->where('id = :id', ['id' => (int)$id]);
        $result = Entity::executeQuery($query);
        self::assertTrue($result);

        $query = $queryBuilder
            ->newSelect()
            ->from(Entity::getTableName())
            ->cols(['*'])
            ->where('id = :id', ['id' => (int)$id]);
        $result = Entity::executeQuery($query);
        self::assertEmpty($result);
    }
}
