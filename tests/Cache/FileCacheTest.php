<?php

namespace Jinya\Database\Cache;

use PHPUnit\Framework\TestCase;

use function Jinya\Database\configure_jinya_database;

class FileCacheTest extends TestCase
{
    public function testEntry(): void
    {
        /** @var array<string, string> $cachedData */
        $cachedData = FileCache::entry(
            __FILE__,
            __NAMESPACE__,
            __CLASS__,
            'TestEntry',
            static function (
                string $filename,
                string $namespace,
                string $class,
                string $key,
                string $cacheClass
            ): string {
                return <<<PHP
<?php
global \$$cacheClass;

\$$cacheClass = ['test' => 'Hello World!'];
PHP;
            }
        );

        self::assertArrayHasKey('test', $cachedData);
        self::assertEquals('Hello World!', $cachedData['test']);
    }

    public function testEntryRecreate(): void
    {
        /** @var array<string, string> $cachedData */
        $cachedData = FileCache::entry(
            __FILE__,
            __NAMESPACE__,
            __CLASS__,
            'TestEntry',
            static function (
                string $filename,
                string $namespace,
                string $class,
                string $key,
                string $cacheClass
            ): string {
                return <<<PHP
<?php
global \$$cacheClass;

\$$cacheClass = ['test' => 'Hello World!'];
PHP;
            }
        );

        self::assertArrayHasKey('test', $cachedData);
        self::assertEquals('Hello World!', $cachedData['test']);

        /** @var array<string, string> $cachedData */
        $cachedData = FileCache::entry(
            __FILE__,
            __NAMESPACE__,
            __CLASS__,
            'TestEntryRecreate',
            static function (
                string $filename,
                string $namespace,
                string $class,
                string $key,
                string $cacheClass
            ): string {
                return <<<PHP
<?php
global \$$cacheClass;

\$$cacheClass = ['test' => 'Goodbye World!'];
PHP;
            },
            true
        );

        self::assertArrayHasKey('test', $cachedData);
        self::assertEquals('Goodbye World!', $cachedData['test']);
    }

    public function testEntryDefaultCacheDirectory(): void
    {
        KeyCache::unset('___Config', 'CacheDirectory');

        /** @var array<string, string> $cachedData */
        $cachedData = FileCache::entry(
            __FILE__,
            __NAMESPACE__,
            __CLASS__,
            'TestEntry',
            static function (
                string $filename,
                string $namespace,
                string $class,
                string $key,
                string $cacheClass
            ): string {
                return <<<PHP
<?php
global \$$cacheClass;

\$$cacheClass = ['test' => 'Hello World!'];
PHP;
            }
        );

        self::assertArrayHasKey('test', $cachedData);
        self::assertEquals('Hello World!', $cachedData['test']);

        /** @phpstan-ignore-next-line */
        configure_jinya_database(getenv('CACHE_DIRECTORY'), getenv('DATABASE_DSN'));
    }
}
