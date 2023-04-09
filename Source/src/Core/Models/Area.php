<?php

namespace Api\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use SoftDeletes;

    protected $table = 'areas';
    protected $fillable = ['name', 'areaPoints'];

    public static function convertPointToString(array $point)
    {
        return '(' . $point['longitude'] . ', ' . $point['latitude'] . ')';
    }

    public static function convertManyPointsToString(array $points)
    {
        return '(' . implode(', ', array_map([self::class, 'convertPointToString'], $points)) . ')';
    }

    public static function convertStringToPoints(string $DBString)
    {
        /* @TODO Remove, it was for tests */
        //$DBString = '((-179, -29), (-175.75, -29), (-172.5, -29), (-169.25, -29), (-166, -29))';
        $DBString = substr($DBString, 1, strlen($DBString) - 2);

        preg_match_all('/(\((?<longitude>.+?), (?<latitude>.+?)\))/', $DBString, $matches);
        return array_map(fn($longitude, $latitude) => ['longitude' => $longitude, 'latitude' => $latitude],
            $matches['longitude'], $matches['latitude']);
    }
}
