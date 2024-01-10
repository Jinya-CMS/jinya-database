<?php

namespace Jinya\Database;

use Jinya\Database\Exception\NotNullViolationException;
use PDOException;

trait CreatableEntityTrait
{
    use FindableEntityTrait;

    /**
     * Creates the given entity
     *
     * @return void
     * @throws PDOException
     * @throws NotNullViolationException
     */
    public function create(): void
    {
        $this->checkRequiredColumns();
        $row = $this->toSqlArray();

        $statement = CreatableEntity::getQueryBuilder()
            ->newInsert()
            ->into(CreatableEntity::getTableName())
            ->addRow($row);

        self::getPDO()->prepare($statement->getStatement())->execute($statement->getBindValues());
        $id = self::getPDO()->lastInsertId();

        $createdEntity = self::findById($id);
        $columns = self::getColumns();
        foreach ($columns['byProperty'] as $column) {
            $this->{$column->propertyName} = $createdEntity->{$column->propertyName};
        }
    }
}
