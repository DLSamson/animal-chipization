<?php

namespace Api\Core\Services\Formatters;

use Api\Core\Models\Account;
use Illuminate\Database\Eloquent\Collection;

class AccountFormatter
{
    public static function PrepareOne(Account $account)
    {
        return [
            'id' => $account->id,
            'firstName' => $account->firstName,
            'lastName' => $account->lastName,
            'email' => $account->email,
            'role' => $account->role,
        ];
    }

    public static function PrepareMany(Collection $accounts)
    {
        return array_map(function ($account) {
            return [
                'id' => $account['id'],
                'firstName' => $account['firstName'],
                'lastName' => $account['lastName'],
                'email' => $account['email'],
                'role' => $account['role'],
            ];
        }, $accounts->toArray());
    }
}