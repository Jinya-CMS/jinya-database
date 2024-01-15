<?php

namespace Jinya\Database\Migration;

use Aura\SqlQuery\QueryFactory;
use PDO;

use function Jinya\Database\getPdo;

abstract class Migrator
{
    private static function createMigrationsStateTable(string $tableName): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS $tableName (
    version varchar(255) not null primary key
)
SQL;

        $pdo = getPdo();
        $pdo->exec($sql);
    }

    private static function getQueryBuilder(): QueryFactory
    {
        /** @var string $driver */
        $driver = getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);

        return new QueryFactory($driver);
    }

    private static function migrationExists(string $tableName, string $migrationName): bool
    {
        $queryBuilder = self::getQueryBuilder();
        $query = $queryBuilder
            ->newSelect()
            ->from($tableName)
            ->cols(['version']);

        $pdo = getPdo();
        $statement = $pdo->prepare($query->getStatement());
        if ($statement === false) {
            return false;
        }

        $result = $statement->execute($query->getBindValues());
        if ($result === false) {
            return false;
        }

        $data = $statement->fetchAll(PDO::FETCH_COLUMN);

        return in_array($migrationName, $data, true);
    }

    private static function markMigrationMigrated(string $tableName, string $migrationName): void
    {
        $queryBuilder = self::getQueryBuilder();
        $query = $queryBuilder
            ->newInsert()
            ->into($tableName)
            ->col('version', $migrationName);

        $pdo = getPdo();
        $statement = $pdo->prepare($query->getStatement());
        if ($statement === false) {
            return;
        }

        $statement->execute($query->getBindValues());
    }

    private static function markMigrationRemoved(string $tableName, string $migrationName): void
    {
        $queryBuilder = self::getQueryBuilder();
        $query = $queryBuilder
            ->newDelete()
            ->from($tableName)
            ->where('version = ?', [$migrationName]);

        $pdo = getPdo();
        $statement = $pdo->prepare($query->getStatement());
        if ($statement === false) {
            return;
        }

        $statement->execute($query->getBindValues());
    }

    /**
     * Executes all migrations in the migrations array if they weren't migrated before
     *
     * @param AbstractMigration[] $migrations
     * @param string $migrationTableName
     * @return void
     */
    public static function migrateUp(array $migrations, string $migrationTableName = 'jinya_migration_state'): void
    {
        self::createMigrationsStateTable($migrationTableName);
        $pdo = getPdo();
        foreach ($migrations as $key => $migration) {
            if ($migration instanceof AbstractMigration) {
                $migrationName = is_string($key) ? $key : $migration->getMigrationName();
                if (!self::migrationExists($migrationTableName, $migrationName)) {
                    $migration->up($pdo);
                    self::markMigrationMigrated($migrationTableName, $migrationName);
                }
            }
        }
    }

    /**
     * Removes all the migrations in the migrations array if the migration was migrated
     *
     * @param AbstractMigration[] $migrations
     * @param string $migrationTableName
     * @return void
     */
    public static function migrateDown(array $migrations, string $migrationTableName = 'jinya_migration_state'): void
    {
        self::createMigrationsStateTable($migrationTableName);
        $pdo = getPdo();
        foreach ($migrations as $key => $migration) {
            if ($migration instanceof AbstractMigration) {
                $migrationName = is_string($key) ? $key : $migration->getMigrationName();
                if (self::migrationExists($migrationTableName, $migrationName)) {
                    $migration->down($pdo);
                    self::markMigrationRemoved($migrationTableName, $migrationName);
                }
            }
        }
    }
}
