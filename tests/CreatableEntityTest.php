<?php

namespace Jinya\Database;

use DateTime;
use Jinya\Database\Exception\NotNullViolationException;
use Jinya\Database\Extensions\MigratingTestCase;
use Jinya\Database\Migrations\CreatableEntityMigration;

class CreatableEntityTest extends MigratingTestCase
{
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

    protected function getMigrations(): array
    {
        return [
            new CreatableEntityMigration(),
        ];
    }
}
