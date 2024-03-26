<?php

namespace Jinya\Database;

use Jinya\Database\Exception\ForeignKeyFailedException;
use PDOException;

trait DeletableEntityTrait
{
    use EntityTrait;

    /**
     * Deletes the current entity
     *
     * @return void
     * @throws ForeignKeyFailedException
     * @throws PDOException
     */
    public function delete(): void
    {
        try {
            $where = $this->getWhereToIdentifyEntity();
            $delete = self::getQueryBuilder()
                ->newDelete()
                ->from(self::getTableName());

            foreach ($where['conditions'] as $condition) {
                $delete->where($condition);
            }

            $delete->bindValues($where['values']);

            self::getPDO()->prepare($delete->getStatement())->execute($delete->getBindValues());
        } catch (PDOException $exception) {
            $errorInfo = $exception->errorInfo ?? ['', ''];
            if ($errorInfo[0] === '23503' || ($errorInfo[0] === '23000' && ($errorInfo[1] === 1451 || $errorInfo[1] === 19))) {
                throw new ForeignKeyFailedException($exception, self::getPDO());
            }

            throw $exception;
        }
    }
}
