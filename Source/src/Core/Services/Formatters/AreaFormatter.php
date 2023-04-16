<?php

namespace Api\Core\Services\Formatters;

use Api\Core\Models\Area;
use Illuminate\Database\Eloquent\Model;

class AreaFormatter extends BaseFormatter
{
    /**
     * @param Area $model
     * @return array
     */
    public static function PrepareOne(Model $model): array
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'areaPoints' => Area::convertStringToPoints($model->areaPoints),
        ];
    }
}