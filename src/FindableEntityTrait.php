<?php

namespace Jinya\Database;

use Iterator;

trait FindableEntityTrait
{
    use EntityTrait;

    /**
     * Finds all entities for the current type
     *
     * @param string $orderBy
     * @return Iterator<self>
     */
    public static function findAll(string $orderBy = 'id ASC'): Iterator
    {
        $query = self::getQueryBuilder()
            ->newSelect()
            ->from(self::getTableName())
            ->cols(self::getSqlColumnNames())
            ->orderBy([$orderBy]);

        /** @var array<array<array-key, mixed>> $data */
        $data = self::executeQuery($query);
        foreach ($data as $item) {
            yield self::fromArray($item);
        }
    }

    /**
     * Finds the entity by the given id
     *
     * @param string|int $id
     * @return self|null
     */
    public static function findById(string|int $id): mixed
    {
        $query = self::getQueryBuilder()
            ->newSelect()
            ->from(self::getTableName())
            ->cols(self::getSqlColumnNames())
            ->where('id = ?', [$id]);

        /** @var array<array<array-key, mixed>> $data */
        $data = self::executeQuery($query);
        if (empty($data)) {
            return null;
        }

        return self::fromArray($data[0]);
    }

    /**
     * Finds all entities in the given range
     *
     * @param int $start
     * @param int $count
     * @param string $orderBy
     * @return Iterator<self>
     */
    public static function findRange(int $start, int $count, string $orderBy = 'id ASC'): Iterator
    {
        $query = self::getQueryBuilder()
            ->newSelect()
            ->from(self::getTableName())
            ->cols(self::getSqlColumnNames())
            ->orderBy([$orderBy])
            ->limit($count)
            ->offset($start);

        /** @var array<array<array-key, mixed>> $data */
        $data = self::executeQuery($query);
        foreach ($data as $item) {
            yield self::fromArray($item);
        }
    }

    /**
     * Finds the entity by the given filters
     *
     * @param array<array{string, array<array-key, mixed>}> $filters
     * @param string $orderBy
     * @return Iterator<self>
     */
    public static function findByFilters(array $filters, string $orderBy = 'id ASC'): Iterator
    {
        $query = self::getQueryBuilder()
            ->newSelect()
            ->from(self::getTableName())
            ->cols(self::getSqlColumnNames())
            ->orderBy([$orderBy]);

        foreach ($filters as $filter) {
            $query = $query->where($filter[0], $filter[1]);
        }

        /** @var array<array<array-key, mixed>> $data */
        $data = self::executeQuery($query);
        foreach ($data as $item) {
            yield self::fromArray($item);
        }
    }
}
