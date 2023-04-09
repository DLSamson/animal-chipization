<?php

namespace Api\Core\Services\Formatters;

use Api\Core\Models\Animal;
use Api\Core\Models\AnimalLocation;
use Illuminate\Database\Eloquent\Collection;

class AnimalFormatter
{
    public static function PrepareOne(Animal $animal)
    {
        return [
            'id' => $animal->id,
            'animalTypes' => $animal->types()
                ->get()->map(fn($el) => $el->id)->toArray(),
            'weight' => $animal->weight,
            'length' => $animal->length,
            'height' => $animal->height,
            'gender' => $animal->gender,
            'lifeStatus' => $animal->lifeStatus,
            'chippingDateTime' => DateFormatter::formatToISO8601($animal->chippingDateTime),
            'chipperId' => $animal->chipperId,
            'chippingLocationId' => $animal->chippingLocationId,
            'visitedLocations' => AnimalLocation::where(['animal_id' => $animal->id])
                ->get()->map(fn($el) => $el->id),
            'deathDateTime' => $animal->lifeStatus == Animal::LIFE_STATUS_DEAD
                ? DateFormatter::formatToISO8601($animal->deathDateTime)
                : null,
        ];
    }

    public static function PrepareMany(Collection $animals)
    {
        return $animals->map(function ($animal) {
            return self::PrepareOne($animal);
        })->toArray();
    }
}