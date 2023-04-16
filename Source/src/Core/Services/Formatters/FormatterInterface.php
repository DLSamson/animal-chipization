<?php

namespace Api\Core\Services\Formatters;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface FormatterInterface
{
    /**
     * @param Model $model
     * @return array
     */
    public static function PrepareOne(Model $model): array;

    /**
     * @param Collection $collection
     * @return array
     */
    public static function PrepareMany(Collection $collection): array;
}