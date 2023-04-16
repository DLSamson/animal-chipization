<?php

namespace Api\Core\Services\Formatters;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseFormatter implements FormatterInterface
{
    /**
     * @param Model $model
     * @return array
     */
    public static function PrepareOne(Model $model): array
    {
        return [];
    }

    /**
     * @param Collection $collection
     * @return array
     */
    public static function PrepareMany(Collection $collection): array
    {
        return $collection->map(function ($model) {
            return static::PrepareOne($model);
        })->toArray();
    }
}