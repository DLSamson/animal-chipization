<?php

namespace Api\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use SoftDeletes;

    protected $table = 'locations';
    protected $fillable = ['latitude', 'longitude'];

    public function chippedAnimals(): HasMany
    {
        return $this->hasMany(Animal::class, 'chippingLocationId', 'id');
    }
}