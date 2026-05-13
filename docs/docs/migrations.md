# Migrations

Jinya Database includes a simple migration system to manage your database schema changes over time.

## Creating a Migration

To create a migration, create a class that extends `Jinya\Database\Migration\AbstractMigration`. You need to implement two methods: `up(PDO $pdo)` and `down(PDO $pdo)`.

```php
namespace App\Migrations;

use Jinya\Database\Migration\AbstractMigration;
use PDO;

class CreateArtistTable extends AbstractMigration
{
    public function up(PDO $pdo): void
    {
        $pdo->exec('CREATE TABLE artists (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255)
        )');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE artists');
    }
}
```

## Running Migrations

To run migrations, use the `Jinya\Database\Migration\Migrator` class. You provide an array of migration instances to the `migrateUp` method.

```php
use App\Migrations\CreateArtistTable;
use Jinya\Database\Migration\Migrator;

$migrations = [
    new CreateArtistTable(),
    // Add more migrations here
];

// Run all pending migrations
Migrator::migrateUp($migrations);
```

### Migration State

Jinya Database keeps track of which migrations have already been executed in a special table called `jinya_migration_state` by default. You can change this table name by passing it as the second argument to `migrateUp`.

```php
Migrator::migrateUp($migrations, 'my_custom_migration_table');
```

## Rolling Back Migrations

If you need to roll back migrations, use the `migrateDown` method.

```php
Migrator::migrateDown($migrations);
```

This will call the `down()` method of each migration in the array, provided it has been previously migrated.
