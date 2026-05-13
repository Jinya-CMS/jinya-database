# Query Builder

Jinya Database integrates with the [Aura.Sql-Query](https://github.com/auraphp/Aura.SqlQuery) library to provide a powerful and flexible query builder.

## Basic Usage

You can get an instance of the query builder from any entity using the `getQueryBuilder()` method.

```php
use App\Entities\Artist;

$queryBuilder = Artist::getQueryBuilder();
```

The `getQueryBuilder()` method automatically detects the database driver from your PDO connection and returns the appropriate query factory.

## Select Queries

You can use the query builder to create complex select queries.

```php
$select = Artist::getQueryBuilder()->newSelect();
$select->from(Artist::getTableName())
       ->cols(['*'])
       ->where('name LIKE ?', ['Jinya%'])
       ->orderBy(['name ASC'])
       ->limit(10);

// Execute the query
$results = Artist::executeQuery($select);

foreach ($results as $row) {
    $artist = Artist::fromArray($row);
    echo $artist->name;
}
```

## Find by Filters

The `FindableEntityTrait` (which `Entity` uses) provides a convenient `findByFilters` method that uses the query builder internally.

```php
// Find artists where name starts with 'J'
$filters = [
    'name LIKE ?' => ['J%'],
];

$artists = Artist::findByFilters($filters, orderBy: 'name DESC');
```

## Custom Queries

You can also execute raw queries or use the query builder for `INSERT`, `UPDATE`, and `DELETE` operations if the standard entity methods don't fit your needs.

```php
$insert = Artist::getQueryBuilder()->newInsert();
$insert->into(Artist::getTableName())
       ->cols([
           'name' => 'New Artist',
           'email' => 'artist@example.com'
       ]);

Artist::executeQuery($insert);
```

The `executeQuery` method returns:
- An array of associative arrays for `SELECT` queries.
- The last insert ID for `INSERT` queries.
- `true` for `UPDATE` and `DELETE` queries.
