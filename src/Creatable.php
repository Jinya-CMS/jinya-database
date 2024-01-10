<?php

namespace Jinya\Database;

interface Creatable
{
    /**
     * Creates the given entity
     */
    public function create(): void;
}
