<?php

namespace Jinya\Database;

use PDOException;

trait DeletableEntityTrait
{
    use EntityTrait;

    /**
     * Deletes the current entity
     *
     * @return void
     * @throws PDOException
     */
    public function delete(): void
    {
        $where = self::getWhereToIdentifyEntity();

        $statement = $this->getQueryBuilder()
            ->newDelete()
            ->from(self::getTableName());

        foreach ($where['conditions'] as $condition) {
            $statement->where($condition);
        }

        $statement->bindValues($where['values']);

        self::getPDO()->prepare($statement->getStatement())->execute($statement->getBindValues());
    }
}
