<?php

namespace Api\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

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

    public function isChipper()
    {
        return $this->role === Role::CHIPPER;
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