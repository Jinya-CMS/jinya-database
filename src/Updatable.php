<?php

namespace Jinya\Database;

interface Updatable
{
    /**
     * Updates the current entity
     */
    public function update(): void;
}
