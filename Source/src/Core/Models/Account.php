<?php

namespace Api\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model {
    protected $table= 'accounts';
    protected $fillable = [
        'firstName',
        'lastName',
        'email',
        'password',
        'role_id',
    ];

    public static function HashPassword($password) {
        return password_hash((string) $password, PASSWORD_DEFAULT);
    }

    public function role() : BelongsTo {
        return $this->belongsTo(Role::class, 'id', 'role_id');
    }
}