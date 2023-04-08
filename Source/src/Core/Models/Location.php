<?php

namespace Api\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $table = 'locations';
    protected $fillable = ['latitude', 'longitude'];

    public function chippedAnimals(): HasMany
    {
        return $this->hasMany(Animal::class, 'chippingLocationId', 'id');
    }
}