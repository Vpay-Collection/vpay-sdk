<?php

namespace Ankio\objects;

class BaseObject
{
    public function __construct($args)
    {
        foreach ($args as $key => $item) {
            if (isset($this->$key)) {
                if(is_string($this->$key)){
                    $this->$key = strval($item);
                }elseif(is_int($this->$key)){
                    $this->$key = intval($item);
                }elseif(is_bool($this->$key)){
                    $this->$key = boolval($item);
                }elseif(is_float($this->$key)){
                    $this->$key = floatval($item);
                }
            }
        }
    }
    function toArray(): array
    {
        return get_object_vars($this);
    }
}