<?php

namespace Jinya\Database;

use DateTime;
use PHPUnit\Framework\TestCase;

class FindableEntityTest extends TestCase
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

        $tableName = FindableEntity::getTableName();
        FindableEntity::getPDO()->exec(
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
        FindableEntity::getPDO()->prepare($statement->getStatement())->execute($statement->getBindValues());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $tableName = FindableEntity::getTableName();
        FindableEntity::getPDO()->exec("drop table $tableName");
    }

    public function testFindById(): void
    {
        $entity = FindableEntity::findById(1);

        self::assertNotNull($entity);
        self::assertEquals('Test 11', $entity->name);
        self::assertEquals('Test case 11', $entity->displayName);
        self::assertEquals(DateTime::createFromFormat('Y-m-d H:i:s', '2011-09-11 20:34:25'), $entity->date);
    }

    public function testFindByIdNotFound(): void
    {
        $entity = FindableEntity::findById(-1);

        self::assertNull($entity);
    }

    public function testFindRange(): void
    {
        $entities = iterator_to_array(FindableEntity::findRange(2, 2));

        self::assertCount(2, $entities);
        self::assertEquals(3, $entities[0]->id);
    }

    public function testFindRangeOrderIdDesc(): void
    {
        $entities = iterator_to_array(FindableEntity::findRange(2, 2, 'id DESC'));

        self::assertCount(2, $entities);
        self::assertEquals(8, $entities[0]->id);
    }

    public function testFindRangeEmptyRange(): void
    {
        $entities = iterator_to_array(FindableEntity::findRange(50, 2));

        self::assertCount(0, $entities);
    }

    public function testFindByFilters(): void
    {
        $entities = iterator_to_array(FindableEntity::findByFilters(['name = :name' => ['name' => 'Test 11']]));

        self::assertCount(1, $entities);
        self::assertEquals(1, $entities[0]->id);
    }

    public function testFindByFiltersOrderById(): void
    {
        $entities = iterator_to_array(FindableEntity::findByFilters(['name LIKE :name' => ['name' => 'Test 1%']], 'id DESC'));

        self::assertCount(9, $entities);
        self::assertEquals(9, $entities[0]->id);
    }

    public function testFindAll(): void
    {
        $entities = iterator_to_array(FindableEntity::findAll());

        self::assertCount(10, $entities);
        self::assertEquals(1, $entities[0]->id);
    }

    public function testFindAllOrderIdDesc(): void
    {
        $entities = iterator_to_array(FindableEntity::findAll('id DESC'));

        self::assertCount(10, $entities);
        self::assertEquals(10, $entities[0]->id);
    }
}
