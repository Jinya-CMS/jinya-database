<?php

namespace Jinya\Database;

interface Deletable
{
    /**
     * Deletes the current entity
     */
    public function delete(): void;
}
