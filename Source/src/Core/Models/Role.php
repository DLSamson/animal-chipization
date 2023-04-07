<?php

namespace Api\Core\Models;

use Api\Core\Models\Account;
use Illuminate\Database\Query\Builder;

class Role
{
    const ADMIN = 'ADMIN';
    const CHIPPER = 'CHIPPER';
    const USER = 'USER';
    
    const DEFAULT = self::USER;

    /**
     * @param string $role
     * @return \Illuminate\Database\Query\Builder
     */
    public static function accounts(string $role): Builder
    {
        return Account::where('role', $role);
    }
}
