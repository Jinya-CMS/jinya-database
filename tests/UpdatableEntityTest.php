<?php

namespace Jinya\Database;

use Jinya\Database\Extensions\MigratingTestCase;
use Jinya\Database\Migrations\UpdatableEntityMigration;

class UpdatableEntityTest extends MigratingTestCase
{
    public function testUpdateWithId(): void
    {
        $row = ['name' => 'Test', 'display_name' => 'Test case', 'date' => '2022-09-11 20:34:25'];
        $statement = UpdatableEntityWithId::getQueryBuilder()
            ->newInsert()
            ->into(UpdatableEntityWithId::getTableName())
            ->addRow($row);
        UpdatableEntityWithId::getPDO()->prepare($statement->getStatement())->execute($statement->getBindValues());

        $entity = UpdatableEntityWithId::findById(1);
        $entity->displayName = 'Test case 1';
        $entity->update();

        $entity = UpdatableEntityWithId::findById(1);
        self::assertEquals('Test case 1', $entity->displayName);
    }

    public function testUpdateWithoutIdWithUniqueColumn(): void
    {
        $rows = [
            ['name' => 'Test', 'display_name' => 'Test case', 'date' => '2022-09-11 20:34:25'],
            ['name' => 'Test 2', 'display_name' => 'Test case', 'date' => '2022-09-11 20:34:25'],
        ];
        $statement = UpdatableEntityWithoutIdWithUniqueColumn::getQueryBuilder()
            ->newInsert()
            ->into(UpdatableEntityWithoutIdWithUniqueColumn::getTableName())
            ->addRows($rows);
        UpdatableEntityWithoutIdWithUniqueColumn::getPDO()->prepare($statement->getStatement())->execute(
            $statement->getBindValues()
        );

        $entity = UpdatableEntityWithoutIdWithUniqueColumn::findByFilters(
            ['name = ?' => ['Test']],
            'name ASC'
        )->current();
        $entity->displayName = 'Test case 1';
        $entity->update();

        $iterator = UpdatableEntityWithoutIdWithUniqueColumn::findByFilters(['name = ?' => ['Test 2']], 'name ASC');
        $entity = $iterator->current();
        self::assertEquals('Test case', $entity->displayName);
    }

    public function testUpdateWithoutIdWithoutUniqueColumn(): void
    {
        $rows = [
            ['name' => 'Test', 'display_name' => 'Test case', 'date' => '2022-09-11 20:34:25'],
            ['name' => 'Test 2', 'display_name' => 'Test case', 'date' => '2022-09-11 20:34:25'],
        ];
        $statement = UpdatableEntityWithoutIdWithoutUniqueColumn::getQueryBuilder()
            ->newInsert()
            ->into(UpdatableEntityWithoutIdWithoutUniqueColumn::getTableName())
            ->addRows($rows);
        UpdatableEntityWithoutIdWithoutUniqueColumn::getPDO()->prepare($statement->getStatement())->execute(
            $statement->getBindValues()
        );

        $entity = UpdatableEntityWithoutIdWithoutUniqueColumn::findByFilters(
            ['name = ?' => ['Test']],
            'name ASC'
        )->current();
        $entity->displayName = 'Test case 1';
        $entity->update();

        $iterator = UpdatableEntityWithoutIdWithoutUniqueColumn::findByFilters(['name = ?' => ['Test']], 'name ASC');
        $entity = $iterator->current();
        self::assertEquals('Test case', $entity->displayName);
    }

    protected function getMigrations(): array
    {
        return [
            new UpdatableEntityMigration()
        ];
    }
}
