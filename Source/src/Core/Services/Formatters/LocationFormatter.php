<?php

namespace Api\Core\Services\Formatters;

use Api\Core\Models\Location;
use Illuminate\Database\Eloquent\Model;

class LocationFormatter extends BaseFormatter
{
    /**
     * @param Location $model
     * @return array
     */
    public static function PrepareOne(Model $model): array
    {
        return [
            'id' => $model->id,
            'longitude' => $model->longitude,
            'latitude' => $model->latitude,
        ];
    }
}