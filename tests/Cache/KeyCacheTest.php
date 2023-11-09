<?php

namespace Jinya\Database\Cache;

use PHPUnit\Framework\TestCase;
use stdClass;

class KeyCacheTest extends TestCase
{
    public function testGetNotExists(): void
    {
        $value = KeyCache::get('test', 'testGetNotExists');
        self::assertNull($value);
    }

    public function testGetString(): void
    {
        $string = 'test';
        KeyCache::entry('test', 'testGetString', static fn (string $key) => $string);

        $value = KeyCache::get('test', 'testGetString');
        self::assertEquals($string, $value);
    }

    public function testGetNumber(): void
    {
        $number = 5;
        KeyCache::entry('test', 'testGetNumber', static fn (string $key) => $number);

        $value = KeyCache::get('test', 'testGetNumber');
        self::assertEquals($number, $value);
    }

    public function testGetArray(): void
    {
        $array = ['test' => 'test'];
        KeyCache::entry('test', 'testGetArray', static fn (string $key) => $array);

        $value = KeyCache::get('test', 'testGetArray');
        self::assertEquals($array, $value);
    }

    public function testGetClass(): void
    {
        $class = new stdClass();
        KeyCache::entry('test', 'testGetClass', static fn (string $key) => $class);

        $value = KeyCache::get('test', 'testGetClass');
        self::assertEquals($class, $value);
    }

    public function testEntryString(): void
    {
        $string = 'test';
        $value = KeyCache::entry('test', 'testEntryString', static fn (string $key) => $string);
        self::assertEquals($string, $value);
    }

    public function testEntryDontSetIfExists(): void
    {
        $string = 'test';
        KeyCache::entry('test', 'testEntryDontSetIfExists', static fn (string $key) => $string);

        KeyCache::entry('test', 'testEntryDontSetIfExists', static function (string $key) {
            self::fail();
        });

        self::assertTrue(true);
    }

    public function testEntryNumber(): void
    {
        $number = 5;
        $value = KeyCache::entry('test', 'testEntryNumber', static fn (string $key) => $number);
        self::assertEquals(5, $value);
    }

    public function testEntryArray(): void
    {
        $array = ['test' => 'test'];
        $value = KeyCache::entry('test', 'testEntryArray', static fn (string $key) => $array);
        self::assertEquals($array, $value);
    }

    public function testEntryClass(): void
    {
        $class = new stdClass();
        $value = KeyCache::entry('test', 'testEntryClass', static fn (string $key) => $class);
        self::assertEquals($class, $value);
    }

    public function testUnset(): void
    {
        $string = 'test';
        $value = KeyCache::entry('test', 'testUnset', static fn (string $key) => $string);
        self::assertEquals($string, $value);

        KeyCache::unset('test', 'testUnset');
        self::assertNull(KeyCache::get('test', 'testUnset'));
    }
}
