<?php

namespace Jinya\Database;

use Jinya\Database\Exception\ForeignKeyFailedException;
use Jinya\Database\Exception\NotNullViolationException;
use Jinya\Database\Exception\UniqueFailedException;
use PDOException;

trait CreatableEntityTrait
{
    use FindableEntityTrait;

    /**
     * Creates the current entity
     *
     * @return void
     * @throws NotNullViolationException
     * @throws UniqueFailedException
     * @throws ForeignKeyFailedException
     * @throws PDOException
     */
    public function create(): void
    {
        try {
            $this->checkRequiredColumns();
            $row = $this->toSqlArray();

            $insert = self::getQueryBuilder()
                ->newInsert()
                ->into(self::getTableName())
                ->addRow($row);

            self::getPDO()->prepare($insert->getStatement())->execute($insert->getBindValues());
            /** @var string|null $lastInsertId */
            $lastInsertId = $insert->getLastInsertIdName(self::getIdProperty()['sqlName'] ?? 'id');
            $id = self::getPDO()->lastInsertId($lastInsertId) ?: '';

            $createdEntity = self::findById($id);
            $columns = self::getColumns();
            foreach ($columns['byProperty'] as $column) {
                $this->{$column->propertyName} = $createdEntity->{$column->propertyName};
            }
        } catch (PDOException $exception) {
            $errorInfo = $exception->errorInfo ?? ['', ''];
            if ($errorInfo[0] === '23505' || ($errorInfo[0] === '23000' && ($errorInfo[1] === 1062 || $errorInfo[1] === 19))) {
                throw new UniqueFailedException($exception, self::getPDO());
            }

            if ($errorInfo[0] === '23503' || ($errorInfo[0] === '23000' && $errorInfo[1] === 1452)) {
                throw new ForeignKeyFailedException($exception, self::getPDO());
            }

            throw $exception;
        }
    }
}
