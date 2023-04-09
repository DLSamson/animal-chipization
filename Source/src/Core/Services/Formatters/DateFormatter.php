<?php

namespace Api\Core\Services\Formatters;

class DateFormatter
{
    public static function formatToISO8601($date)
    {
        return date("c", strtotime($date));
    }
}