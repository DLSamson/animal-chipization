<?php

namespace Api\Core\Services;

/* Since I have no idea how to make php work fine with high precision float values */

class LocationJSONfixer
{
    public static function fix($json)
    {
        $regExp = '/"(latitude|longitude)": (\d+?\.\d+)(,)?/';
        return preg_replace($regExp, '"$1": "$2"$3', $json);
    }
}