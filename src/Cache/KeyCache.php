<?php

namespace Jinya\Database\Cache;

/**
 * @internal
 */
abstract class KeyCache
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private static array $cache = [];

    /**
     * @param string $group
     * @return void
     */
    private static function prepareCache(string $group): void
    {
        if (!array_key_exists($group, self::$cache)) {
            self::$cache[$group] = [];
        }
    }

    /**
     * Gets the given key or stores the return value of the given setter
     *
     * @param string $group
     * @param string $key
     * @param callable(string): mixed $setter
     * @param bool $force
     * @return mixed
     */
    public static function entry(string $group, string $key, callable $setter, bool $force = false): mixed
    {
        self::prepareCache($group);

        if ($force || !array_key_exists($key, self::$cache[$group])) {
            self::$cache[$group][$key] = $setter($key);
        }

        return self::$cache[$group][$key];
    }

    /**
     * Gets the given key from the given group
     *
     * @param string $group
     * @param string $key
     * @return mixed
     */
    public static function get(string $group, string $key): mixed
    {
        self::prepareCache($group);

        return self::$cache[$group][$key] ?? null;
    }

    /**
     * Unset the given key from the given group
     *
     * @param string $group
     * @param string $key
     * @return void
     */
    public static function unset(string $group, string $key): void
    {
        self::prepareCache($group);

        unset(self::$cache[$group][$key]);
    }
}
