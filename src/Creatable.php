<?php

namespace Jinya\Database;

interface Creatable
{
    /**
     * Creates the current entity
     */
    public function create(): void;
}
