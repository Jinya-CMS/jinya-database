<?php

namespace Jinya\Database;

use DateTime;
use Jinya\Database\Extensions\MigratingTestCase;
use Jinya\Database\Migrations\FindableEntityMigration;

class FindableEntityTest extends MigratingTestCase
{
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
        $entities = iterator_to_array(
            FindableEntity::findByFilters(['name LIKE :name' => ['name' => 'Test 1%']], 'id DESC')
        );

        self::assertCount(9, $entities);
        self::assertEquals(9, $entities[0]->id);
    }

    public function testCountByFilters(): void
    {
        $count = FindableEntity::countByFilters(['name = :name' => ['name' => 'Test 11']]);

        self::assertEquals(1, $count);
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

    public function testCountAll(): void
    {
        $count = FindableEntity::countAll();

        self::assertEquals(10, $count);
    }

    protected function getMigrations(): array
    {
        return [
            new FindableEntityMigration()
        ];
    }
}
