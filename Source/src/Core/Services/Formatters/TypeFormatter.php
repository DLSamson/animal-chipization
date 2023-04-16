<?php

namespace Api\Core\Services\Formatters;

use Api\Core\Models\Type;
use Illuminate\Database\Eloquent\Model;

class TypeFormatter extends BaseFormatter
{
    /**
     * @param Type $model
     * @return array
     */
    public static function PrepareOne(Model $model): array
    {
        return [
            'id' => $model->id,
            'type' => $model->type,
        ];
    }
}