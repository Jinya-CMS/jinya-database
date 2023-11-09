<?php

namespace Jinya\Database;

use Iterator;

interface Findable
{
    /**
     * Finds all entities of the current type
     *
     * @param string $orderBy
     * @return Iterator<self>
     */
    public static function findAll(string $orderBy = 'id ASC'): Iterator;

    /**
     * Finds the entity by the given id
     *
     * @param string|int $id
     * @return self
     */
    public static function findById(string|int $id): mixed;

    /**
     * Finds the entity by the given filters
     *
     * @param array<array{string, array<array-key, mixed>}> $filters
     * @param string $orderBy
     * @return Iterator<self>
     */
    public static function findByFilters(array $filters, string $orderBy = 'id ASC'): Iterator;

    /**
     * Finds all entities in the given range
     *
     * @param int $start
     * @param int $count
     * @param string $orderBy
     * @return Iterator<self>
     */
    public static function findRange(int $start, int $count, string $orderBy = 'id ASC'): Iterator;
}
