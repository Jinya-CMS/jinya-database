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
        $statement = $this->getQueryBuilder()
            ->newDelete()
            ->from(self::getTableName())
            ->where(...self::getWhereToIdentifyEntity());

        self::getPDO()->prepare($statement->getStatement())->execute($statement->getBindValues());
    }
}
