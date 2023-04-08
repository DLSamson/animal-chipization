<?php

namespace Api\Core\Services\Formatters;

use Api\Core\Models\Location;
use Illuminate\Database\Eloquent\Collection;

class LocationFormatter
{
    public static function PrepareOne(Location $location)
    {
        return [
            'id' => $location->id,
            'longitude' => $location->longitude,
            'latitude' => $location->latitude,
        ];
    }

    public static function PrepareMany(Collection $accounts)
    {
        return array_map(function ($location) {
            return [
                'id' => $location['id'],
                'longitude' => $location['longitude'],
                'latitude' => $location['latitude'],
            ];
        }, $accounts->toArray());
    }
}