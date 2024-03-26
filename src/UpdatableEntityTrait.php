<?php

namespace Jinya\Database;

use Jinya\Database\Exception\ForeignKeyFailedException;
use Jinya\Database\Exception\NotNullViolationException;
use Jinya\Database\Exception\UniqueFailedException;
use PDOException;

trait UpdatableEntityTrait
{
    use FindableEntityTrait;

    /**
     * Updates the current entity
     *
     * @return void
     * @throws NotNullViolationException
     * @throws UniqueFailedException
     * @throws ForeignKeyFailedException
     * @throws PDOException
     */
    public function update(): void
    {
        try {
            $this->checkRequiredColumns();
            $row = $this->toSqlArray();
            $where = $this->getWhereToIdentifyEntity();

            $statement = self::getQueryBuilder()
                ->newUpdate()
                ->table(self::getTableName())
                ->cols(array_keys($row));
            foreach ($where['conditions'] as $condition) {
                $statement->where($condition);
            }

            $statement->bindValues($where['values']);
            $statement->bindValues($row);

            self::getPDO()->prepare($statement->getStatement())->execute($statement->getBindValues());
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
