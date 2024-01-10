<?php

namespace Jinya\Database;

use DateTime;
use Jinya\Database\Exception\NotNullViolationException;
use PHPUnit\Framework\TestCase;

class CreatableEntityTest extends TestCase
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

        $tableName = CreatableEntity::getTableName();
        CreatableEntity::getPDO()->exec(
            "
        create table $tableName (
            id integer primary key $identity,
            name varchar(255) not null,
            display_name varchar(255),
            date timestamp
        )"
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $tableName = CreatableEntity::getTableName();
        CreatableEntity::getPDO()->exec("drop table $tableName");
    }

    public function testCreateEntityWithAllFields(): void
    {
        $entity = new CreatableEntity();
        $entity->date = new DateTime('now');
        $entity->displayName = 'Test';
        $entity->name = 'Test';
        $entity->create();

        self::assertGreaterThanOrEqual(0, $entity->id);
    }

    public function testCreateEntityWithRequiredFieldsOnly(): void
    {
        $entity = new CreatableEntity();
        $entity->name = 'Test';
        $entity->create();

        self::assertGreaterThanOrEqual(0, $entity->id);
        self::assertNull($entity->displayName);
        self::assertEquals('2021-05-01', $entity->date->format('Y-m-d'));
    }

    public function testCreateEntityWithRequiredFieldMissing(): void
    {
        $this->expectException(NotNullViolationException::class);
        $this->expectExceptionMessage('Columns missing: name');
        $entity = new CreatableEntity();
        $entity->date = new DateTime('now');
        $entity->create();
    }
}
