<?php

namespace Jinya\Database;

use Jinya\Database\Exception\NotNullViolationException;
use PDOException;

trait UpdatableEntityTrait
{
    use FindableEntityTrait;

    /**
     * Updates the current entity
     *
     * @return void
     * @throws PDOException
     * @throws NotNullViolationException
     */
    public function update(): void
    {
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
    }
}
