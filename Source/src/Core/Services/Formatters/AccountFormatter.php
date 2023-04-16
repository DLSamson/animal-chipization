<?php

namespace Api\Core\Services\Formatters;

use Api\Core\Models\Account;
use Illuminate\Database\Eloquent\Model;

class AccountFormatter extends BaseFormatter
{
    /**
     * @param Account $model
     * @return array
     */
    public static function PrepareOne(Model $model): array
    {
        return [
            'id' => $model->id,
            'firstName' => $model->firstName,
            'lastName' => $model->lastName,
            'email' => $model->email,
            'role' => $model->role,
        ];
    }
}