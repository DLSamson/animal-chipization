<?php

namespace Api\Core\Services\Formatters;

use Api\Core\Models\Type;
use Illuminate\Database\Eloquent\Collection;

class TypeFormatter
{
    public static function PrepareOne(Type $type)
    {
        return [
            'id' => $type->id,
            'type' => $type->type,
        ];
    }

    public static function PrepareMany(Collection $types)
    {
        return $types->map(function ($type) {
            return self::PrepareOne($type);
        })->toArray();
    }
}