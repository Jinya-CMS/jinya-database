<?php

namespace Jinya\Database;

use Jinya\Database\Cache\KeyCache;

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
