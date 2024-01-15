<?php

namespace Jinya\Database\Migration;

use Jinya\Database\Migrations\CreatableEntityMigration;
use Jinya\Database\Migrations\FindableEntityMigration;
use Jinya\Database\Migrations\UpdatableEntityMigration;
use PHPUnit\Framework\TestCase;

use function Jinya\Database\getPdo;

class MigratorTest extends TestCase
{
    public function testMigrator(): void
    {
        $migrationsWaveOne = [
            new CreatableEntityMigration(),
            'test' => new UpdatableEntityMigration(),
        ];

        Migrator::migrateUp($migrationsWaveOne);

        $migrationsWaveTwo = [
            'test' => new UpdatableEntityMigration(),
            new FindableEntityMigration(),
        ];

        Migrator::migrateUp($migrationsWaveTwo);

        $stmt = getPdo()->query('SELECT version FROM jinya_migration_state ORDER BY version ASC');
        $stmt->execute();

        $data = $stmt->fetchAll();
        self::assertCount(3, $data);
        self::assertEquals('CreatableEntityMigration', $data[0]['version']);
        self::assertEquals('FindableEntityMigration', $data[1]['version']);
        self::assertEquals('test', $data[2]['version']);

        Migrator::migrateDown($migrationsWaveTwo);

        $stmt = getPdo()->query('SELECT version FROM jinya_migration_state ORDER BY version ASC');
        $stmt->execute();

        $data = $stmt->fetchAll();
        self::assertCount(1, $data);
        self::assertEquals('CreatableEntityMigration', $data[0]['version']);

        Migrator::migrateDown($migrationsWaveOne);

        $stmt = getPdo()->query('SELECT version FROM jinya_migration_state ORDER BY version ASC');
        $stmt->execute();

        $data = $stmt->fetchAll();
        self::assertEmpty($data);
    }
}
