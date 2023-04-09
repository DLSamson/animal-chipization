<?php

namespace Api\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Type extends Model
{
    use SoftDeletes;

    protected $table = 'types';
    protected $fillable = ['type'];

    public function animals(): BelongsToMany
    {
        return $this->belongsToMany(
            Animal::class,
            'animals_types',
            'type_id',
            'animal_id'
        );
    }
}