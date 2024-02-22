<?php

namespace Jinya\Database;

use Jinya\Database\Exception\NotNullViolationException;
use PDOException;

trait CreatableEntityTrait
{
    use FindableEntityTrait;

    /**
     * Creates the current entity
     *
     * @return void
     * @throws PDOException
     * @throws NotNullViolationException
     */
    public function create(): void
    {
        $this->checkRequiredColumns();
        $row = $this->toSqlArray();

        $insert = self::getQueryBuilder()
            ->newInsert()
            ->into(self::getTableName())
            ->addRow($row);

        self::getPDO()->prepare($insert->getStatement())->execute($insert->getBindValues());
        $lastInsertId = $insert->getLastInsertIdName(self::getIdProperty()['sqlName']);
        $id = self::getPDO()->lastInsertId($lastInsertId) ?: '';

        $createdEntity = self::findById($id);
        $columns = self::getColumns();
        foreach ($columns['byProperty'] as $column) {
            $this->{$column->propertyName} = $createdEntity->{$column->propertyName};
        }
    }
}
