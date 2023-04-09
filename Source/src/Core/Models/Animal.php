<?php

namespace Api\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Animal extends Model
{
    use SoftDeletes;

    protected $table = 'animals';
    protected $fillable = [
        'weight', 'length', 'height',
        'gender', 'lifeStatus', 'chipperId',
        'chippingLocationId', 'chippingDateTime'
    ];

    public const DEFAULT_LIFE_STATUS = 'ALIVE';
    public const LIFE_STATUS_ALIVE = 'ALIVE';
    public const LIFE_STATUS_DEAD = 'DEAD';

    public static function genderValues()
    {
        return ['MALE', 'FEMALE', 'OTHER'];
    }

    public static function lifeStatusValues()
    {
        return [self::LIFE_STATUS_DEAD, self::LIFE_STATUS_ALIVE];
    }

    public function location()
    {
        return $this->hasOne(Location::class, 'id', 'chippingLocationId');
    }

    public function locations()
    {
        return $this->belongsToMany(
            Location::class,
            'animals_locations',
            'animal_id',
            'location_id'
        );
    }

    public function types()
    {
        return $this->belongsToMany(
            Type::class,
            'animals_types',
            'animal_id',
            'type_id'
        );
    }
}