<?php

namespace Jinya\Database\Exception;

use Exception;

/**
 * Gets thrown when a not null constraint is violated
 */
class NotNullViolationException extends Exception
{
    /**
     * @param string[] $columns The columns that are null
     */
    public function __construct(public readonly array $columns)
    {
        parent::__construct('Columns missing: ' . implode(', ', $this->columns));
    }

}
