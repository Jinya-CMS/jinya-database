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
            ->orderBy([$orderBy])
            ->getStatement();

        $data = self::executeQuery($query);

        foreach ($data as $item) {
            yield self::mapFindableEntity($item);
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
            ->where('id = :id')
            ->getStatement();

        $data = self::executeQuery($query, ['id' => $id]);

        if (empty($data)) {
            return null;
        }

        return self::mapFindableEntity($data[0]);
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
            ->offset($start)
            ->getStatement();

        $data = self::executeQuery($query);
        foreach ($data as $item) {
            yield self::mapFindableEntity($item);
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

        $query = $query->getStatement();

        $data = self::executeQuery($query);
        foreach ($data as $item) {
            yield self::mapFindableEntity($item);
        }
    }

    /**
     * @param array<string, mixed> $item
     * @return self
     */
    public static function mapFindableEntity(array $item): mixed
    {
        $entity = new self();
        foreach ($item as $key => $value) {
            $column = self::getColumnBySqlName($key);
            if ($column && $column->converter) {
                $entity->{$column->propertyName} = $column->converter->from($value);
            } elseif ($column) {
                $entity->{$column->propertyName} = $value;
            }
        }

        return $entity;
    }
}
