<?php

namespace Jinya\Database;

use Jinya\Database\Converters\DateConverter;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

class EntityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $identity = match (getenv('DATABASE_TYPE')) {
            'mysql' => 'auto_increment',
            'sqlite' => 'autoincrement',
            'pgsql' => 'generated always as identity',
            default => throw new RuntimeException(),
        };

        $tableName = TestEntity::getTableName();
        TestEntity::getPDO()->exec(
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
        TestEntity::getPDO()->prepare($statement->getStatement())->execute($statement->getBindValues());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $tableName = TestEntity::getTableName();
        TestEntity::getPDO()->exec("drop table $tableName");
    }

    public function testGetPDO(): void
    {
        $pdo = TestEntity::getPDO();

        self::assertNotNull($pdo);

        $pdo2 = TestEntity::getPDO();

        self::assertEquals($pdo, $pdo2);
    }

    public function testExecuteQuery(): void
    {
        $queryBuilder = TestEntity::getQueryBuilder();
        $query = $queryBuilder
            ->newInsert()
            ->into(TestEntity::getTableName())
            ->addRow(['name' => 'Test 1', 'display_name' => 'Test case 1', 'date' => '2020-09-11 20:34:25']);
        $id = TestEntity::executeQuery($query);
        self::assertIsString($id);

        $query = $queryBuilder
            ->newSelect()
            ->from(TestEntity::getTableName())
            ->cols(['*'])
            ->where('id = :id', ['id' => (int)$id]);
        /** @var array<array<string, mixed>> $result */
        $result = TestEntity::executeQuery($query);
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
            ->table(TestEntity::getTableName())
            ->cols(['display_name' => 'Test case 4'])
            ->where('id = :id', ['id' => (int)$id]);
        $result = TestEntity::executeQuery($query);
        self::assertTrue($result);

        $query = $queryBuilder
            ->newSelect()
            ->from(TestEntity::getTableName())
            ->cols(['*'])
            ->where('id = :id', ['id' => (int)$id]);
        /** @var array<array<string, mixed>> $result */
        $result = TestEntity::executeQuery($query);
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
            ->from(TestEntity::getTableName())
            ->where('id = :id', ['id' => (int)$id]);
        $result = TestEntity::executeQuery($query);
        self::assertTrue($result);

        $query = $queryBuilder
            ->newSelect()
            ->from(TestEntity::getTableName())
            ->cols(['*'])
            ->where('id = :id', ['id' => (int)$id]);
        $result = TestEntity::executeQuery($query);
        self::assertEmpty($result);
    }

    public function testGetTableName(): void
    {
        $tableName = TestEntity::getTableName();

        self::assertEquals('entity', $tableName);
    }

    public function testGetTableNameNoExplicitName(): void
    {
        $tableName = TestEntityNoExplicitName::getTableName();
        $className = (new ReflectionClass(TestEntityNoExplicitName::class))->getShortName();

        self::assertEquals($className, $tableName);
    }

    public function testFromArray(): void
    {
        $query = TestEntity::getQueryBuilder()
            ->newSelect()
            ->from(TestEntity::getTableName())
            ->cols(['*'])
            ->limit(1)
            ->orderBy(['id ASC']);
        /** @var array<array<string, mixed>> $result */
        $result = TestEntity::executeQuery($query);

        $entity = TestEntity::fromArray($result[0]);
        self::assertEquals($entity->id, $result[0]['id']);
        self::assertEquals($entity->name, $result[0]['name']);
        self::assertEquals($entity->displayName, $result[0]['display_name']);
        /** @phpstan-ignore-next-line */
        self::assertEquals($entity->date, (new DateConverter('Y-m-d H:i:s'))->from($result[0]['date']));
    }
}
