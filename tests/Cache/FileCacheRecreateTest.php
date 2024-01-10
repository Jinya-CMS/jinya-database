<?php

namespace Jinya\Database\Cache;

use PHPUnit\Framework\TestCase;

class FileCacheRecreateTest extends TestCase
{
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
            },
            true
        );

        self::assertArrayHasKey('test', $cachedData);
        self::assertEquals('Hello World!', $cachedData['test']);

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

\$$cacheClass = ['test' => 'Goodbye World!'];
PHP;
            },
            true
        );

        self::assertArrayHasKey('test', $cachedData);
        self::assertEquals('Goodbye World!', $cachedData['test']);

        FileCache::entry(
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
            },
            true
        );
    }
}
