<?php

namespace Api\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnimalLocation extends Model
{
    use SoftDeletes;

    protected $table = 'animals_locations';
    protected $fillable = [
        'animal_id', 'location_id', 'dateTimeOfVisitLocationPoint',
    ];
}