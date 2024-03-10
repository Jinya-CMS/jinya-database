<?php

namespace Jinya\Database\Cache;

use ReflectionClass;
use RuntimeException;

/**
 * @internal
 */
abstract class FileCache
{
    /**
     * @param string $filename
     * @param string $namespace
     * @param class-string $class
     * @param string $key
     * @param callable(string, string, string, string, string): string $setter
     * @param bool $force
     * @return mixed
     * @internal
     */
    public static function entry(
        string $filename,
        string $namespace,
        string $class,
        string $key,
        callable $setter,
        bool $force = false,
    ): mixed {
        $cacheDirectory = KeyCache::entry(
            '___Config',
            'CacheDirectory',
            static fn ($val) => (PHP_SAPI === 'cli' ? getcwd() : $_SERVER['DOCUMENT_ROOT']) . '/var/cache'
        ) . '/jinya/database/' . str_replace('\\', '/', $namespace);

        if (!@mkdir($cacheDirectory, recursive: true) && !is_dir($cacheDirectory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $cacheDirectory));
        }

        $reflectionClass = new ReflectionClass($class);
        $cacheClass = $reflectionClass->getShortName() . "__$key";
        $cachePath = "$cacheDirectory/" . basename($filename) . ".$key.cache.php";
        if ($force || !file_exists($cachePath) || filemtime($cachePath) < filemtime($filename)) {
            $phpCacheContent = $setter($filename, $namespace, $class, $key, $cacheClass);
            file_put_contents($cachePath, $phpCacheContent);
        }

        require_once $cachePath;
        if ($force) {
            require $cachePath;
        }

        global $$cacheClass;

        return $$cacheClass;
    }
}
