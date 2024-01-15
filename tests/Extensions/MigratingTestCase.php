<?php

namespace Jinya\Database\Extensions;

use Jinya\Database\Migration\AbstractMigration;
use Jinya\Database\Migration\Migrator;
use PHPUnit\Framework\TestCase;

abstract class MigratingTestCase extends TestCase
{
    /**
     * @return AbstractMigration[]
     */
    abstract protected function getMigrations(): array;

    protected function setUp(): void
    {
        parent::setUp();
        Migrator::migrateUp($this->getMigrations());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Migrator::migrateDown($this->getMigrations());
    }
}
