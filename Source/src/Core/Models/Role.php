<?php

namespace Api\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $table = 'roles';
    protected $fillable = [
      'name'
    ];

    public function accounts() : BelongsToMany {
        return $this->belongsToMany(Account::class, 'accounts', 'role_id', 'id');
    }
}