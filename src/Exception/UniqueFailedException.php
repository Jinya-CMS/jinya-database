<?php

namespace Jinya\Database\Exception;

use PDO;
use PDOException;

class UniqueFailedException extends PDOException
{
    public function __construct(PDOException $exception, public readonly PDO $pdo)
    {
        parent::__construct($exception->message, $exception->code, $exception->getPrevious());
        $this->errorInfo = $exception->errorInfo;
    }
}
