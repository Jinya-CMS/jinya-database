<?php

namespace Jinya\Database;

interface Deletable
{
    /**
     * Creates the current entity
     */
    public function delete(): void;
}
