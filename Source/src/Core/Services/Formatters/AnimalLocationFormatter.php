<?php

namespace Api\Core\Services\Formatters;

use Api\Core\Models\AnimalLocation;
use Illuminate\Database\Eloquent\Model;

class AnimalLocationFormatter extends BaseFormatter
{
    /**
     * @param AnimalLocation $model
     * @return array
     */
    public static function PrepareOne(Model $model): array
    {
        return [
            'id' => $model->id,
            'dateTimeOfVisitLocationPoint' => DateFormatter::formatToISO8601(
                $model->dateTimeOfVisitLocationPoint),
            'locationPointId' => $model->location_id,
        ];
    }
}