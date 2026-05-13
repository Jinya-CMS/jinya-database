# Caching

Jinya Database uses a two-level caching system to ensure high performance by avoiding redundant reflection and configuration lookups.

## Key Cache

The `KeyCache` is an in-memory cache used for configuration and runtime objects like the PDO instance. It stores data in a static array, which lasts for the duration of the PHP request.

Commonly used groups in `KeyCache` include:
- `___Config`: Stores configuration values set via `configure_jinya_database`.
- `___Database`: Stores the shared `PDO` instance.

## File Cache

The `FileCache` is a persistent cache that stores entity metadata (table names, column mappings, primary keys) as PHP files. This avoids using reflection on every request.

### How it works

When an entity is first accessed, Jinya Database uses reflection to read its attributes. The results are then written to a PHP file in the specified `cacheDirectory`. Subsequent requests will `require` this file instead of using reflection.

The cache files are automatically invalidated if the entity's source file is modified (based on file modification time).

### Cache Directory Structure

The cache is organized by namespace within the `cacheDirectory`:

```text
var/cache/
└── jinya/
    └── database/
        └── App/
            └── Entities/
                └── Artist.php.Columns.cache.php
                └── Artist.php.Id.cache.php
                └── Artist.php.Table.cache.php
```

### Manual Cache Clearance

Since the cache is based on file modification times, you usually don't need to clear it manually during development. However, in production environments where file times might not change as expected during deployment, you may want to clear the cache directory:

```bash
rm -rf var/cache/jinya/database
```
