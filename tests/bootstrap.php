<?php

use function Jinya\Database\configure_jinya_database;

require_once __DIR__ . '/../vendor/autoload.php';

/** @phpstan-ignore-next-line */
configure_jinya_database(getenv('CACHE_DIRECTORY'), getenv('DATABASE_DSN'));
