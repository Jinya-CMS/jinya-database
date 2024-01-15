<?php

namespace Jinya\Database;

use Jinya\Database\Cache\KeyCache;
use PDO;

/**
 * Configures Jinya Database to use the given cache directory, connection string and connection options which are passed to PDO
 *
 * @param string $cacheDirectory
 * @param string $connectionString
 * @param array<string, mixed> $connectionOptions
 * @return void
 */
function configure_jinya_database(
    string $cacheDirectory,
    string $connectionString,
    array $connectionOptions = [],
    bool $enableAutoConvert = true
): void {
    KeyCache::entry('___Config', 'CacheDirectory', static fn (string $key) => $cacheDirectory, true);
    KeyCache::entry('___Config', 'ConnectionString', static fn (string $key) => $connectionString, true);
    KeyCache::entry('___Config', 'ConnectionOptions', static fn (string $key) => $connectionOptions, true);
    KeyCache::entry('___Config', 'EnableAutoConvert', static fn (string $key) => $enableAutoConvert, true);
}

/**
 * @return PDO
 * @internal
 */
function getPdo(): PDO
{
    /** @var PDO $pdo */
    $pdo = KeyCache::entry('___Database', 'PDO', static function (string $key) {
        $options = KeyCache::get('___Config', 'ConnectionOptions');
        $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

        return new PDO(KeyCache::get('___Config', 'ConnectionString'), options: $options);
    });

    return $pdo;
}
