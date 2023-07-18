<?php
/*
 * Copyright (c) 2023. Ankio.  由CleanPHP4强力驱动。
 */

namespace Ankio\objects;

class StateObject extends BaseObject
{
    public int $state = 0;
    public string $return_url = '';
}