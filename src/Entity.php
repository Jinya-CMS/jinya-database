<?php

namespace Jinya\Database;

abstract class Entity implements Creatable, Deletable, Findable, Updatable
{
    use CreatableEntityTrait;
    use DeletableEntityTrait;
    use FindableEntityTrait;
    use UpdatableEntityTrait;
    use EntityTrait;
}
