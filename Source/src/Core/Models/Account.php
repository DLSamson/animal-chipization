<?php

namespace Api\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $table = 'accounts';
    protected $fillable = [
        'firstName',
        'lastName',
        'email',
        'password',
        'role',
    ];

    public function isAdmin()
    {
        return $this->role === Role::ADMIN;
    }

    public static function HashPassword(string $password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function animals(): HasMany
    {
        return $this->hasMany(Animal::class, 'chipperId', 'id');
    }
}