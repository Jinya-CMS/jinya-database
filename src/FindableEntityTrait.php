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
        $select = self::getQueryBuilder()
            ->newSelect()
            ->from(self::getTableName())
            ->cols(self::getSqlColumnNames())
            ->orderBy([$orderBy]);

        /** @var array<array<array-key, mixed>> $data */
        $data = self::executeQuery($select);
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
        $idColumn = self::getIdProperty()['sqlName'] ?? 'id';
        $select = self::getQueryBuilder()
            ->newSelect()
            ->from(self::getTableName())
            ->cols(self::getSqlColumnNames())
            ->where("$idColumn = ?", [$id]);

        /** @var array<array<array-key, mixed>> $data */
        $data = self::executeQuery($select);
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
        $select = self::getQueryBuilder()
            ->newSelect()
            ->from(self::getTableName())
            ->cols(self::getSqlColumnNames())
            ->orderBy([$orderBy])
            ->limit($count)
            ->offset($start);

        /** @var array<array<array-key, mixed>> $data */
        $data = self::executeQuery($select);
        foreach ($data as $item) {
            yield self::fromArray($item);
        }
    }

    /**
     * Finds the entity by the given filters
     *
     * @param array<string, array<string, mixed>> $filters
     * @param string $orderBy
     * @return Iterator<self>
     */
    public static function findByFilters(array $filters, string $orderBy = 'id ASC'): Iterator
    {
        $select = self::getQueryBuilder()
            ->newSelect()
            ->from(self::getTableName())
            ->cols(self::getSqlColumnNames())
            ->orderBy([$orderBy]);

        foreach ($filters as $key => $filter) {
            $select = $select->where($key, $filter);
        }

        /** @var array<array<array-key, mixed>> $data */
        $data = self::executeQuery($select);
        foreach ($data as $item) {
            yield self::fromArray($item);
        }
    }
}
