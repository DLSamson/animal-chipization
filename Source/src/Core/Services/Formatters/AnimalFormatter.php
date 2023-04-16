<?php

namespace Api\Core\Services\Formatters;

use Api\Core\Models\Animal;
use Api\Core\Models\AnimalLocation;
use Illuminate\Database\Eloquent\Model;

class AnimalFormatter extends BaseFormatter
{
    /**
     * @param Animal $model
     * @return array
     */
    public static function PrepareOne(Model $model): array
    {
        return [
            'id' => $model->id,
            'animalTypes' => $model->types()
                ->get()->map(fn($el) => $el->id)->toArray(),
            'weight' => $model->weight,
            'length' => $model->length,
            'height' => $model->height,
            'gender' => $model->gender,
            'lifeStatus' => $model->lifeStatus,
            'chippingDateTime' => DateFormatter::formatToISO8601($model->chippingDateTime),
            'chipperId' => $model->chipperId,
            'chippingLocationId' => $model->chippingLocationId,
            'visitedLocations' => AnimalLocation::where(['animal_id' => $model->id])
                ->get()->map(fn($el) => $el->id),
            'deathDateTime' => $model->lifeStatus == Animal::LIFE_STATUS_DEAD
                ? DateFormatter::formatToISO8601($model->deathDateTime)
                : null,
        ];
    }
}