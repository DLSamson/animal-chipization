<?php

namespace Api\Core\Services\Formatters;

use Api\Core\Models\Area;
use Illuminate\Database\Eloquent\Collection;

class AreaFormatter
{
    public static function PrepareOne(Area $area)
    {
        return [
            'id' => $area->id,
            'name' => $area->name,
            'areaPoints' => Area::convertStringToPoints($area->areaPoints),
        ];
    }

    public static function PrepareMany(Collection $area)
    {
        return $area->map(function ($area) {
            return self::PrepareOne($area);
        })->toArray();
    }
}