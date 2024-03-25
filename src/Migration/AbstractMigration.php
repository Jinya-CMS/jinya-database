<?php

namespace Jinya\Database\Migration;

use PDO;
use ReflectionClass;

/**
 * Basic class for migrations
 */
abstract class AbstractMigration
{
    /**
     * @return string
     * @internal
     */
    public function getMigrationName(): string
    {
        return (new ReflectionClass(static::class))->getShortName();
    }

    /**
     * Migrates the database up
     *
     * @param PDO $pdo
     * @return void
     */
    abstract public function up(PDO $pdo): void;

    /**
     * Migrates the database down
     *
     * @param PDO $pdo
     * @return void
     */
    abstract public function down(PDO $pdo): void;
}
