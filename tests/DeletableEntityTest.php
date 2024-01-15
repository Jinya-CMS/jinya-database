<?php

namespace Jinya\Database;

use Jinya\Database\Extensions\MigratingTestCase;
use Jinya\Database\Migrations\DeletableEntityMigration;

class DeletableEntityTest extends MigratingTestCase
{
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

    protected function getMigrations(): array
    {
        return [
            new DeletableEntityMigration()
        ];
    }
}
