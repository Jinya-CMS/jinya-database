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
        $where = $this->getWhereToIdentifyEntity();
        $delete = self::getQueryBuilder()
            ->newDelete()
            ->from(self::getTableName());

        foreach ($where['conditions'] as $condition) {
            $delete->where($condition);
        }

        $delete->bindValues($where['values']);

        self::getPDO()->prepare($delete->getStatement())->execute($delete->getBindValues());
    }
}
