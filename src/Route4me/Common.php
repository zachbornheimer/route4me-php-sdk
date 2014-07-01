<?php

namespace Route4me;

class Common
{
    static public function getValue($array, $item, $default = null)
    {
        return (isset($array[$item])) ? $array[$item] : $default;
    }

    public function toArray()
    {
        $params = array_filter(get_object_vars($this), function($item) {
            return ($item !== null) && !(is_array($item) && !count($item));
        });

        return $params;
    }
}
