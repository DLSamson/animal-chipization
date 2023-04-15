<?php

namespace Api\Core\Models;

use ErrorException;
use Illuminate\Database\Capsule\Manager;
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

        preg_match_all('/(\((?<longitude>.+?), ?(?<latitude>.+?)\))/', $DBString, $matches);
        return array_map(fn($longitude, $latitude) => ['longitude' => $longitude, 'latitude' => $latitude],
            $matches['longitude'], $matches['latitude']);
    }

    /* Good */
    public static function whereRawRepeat(string $points)
    {
        return self::whereRaw("\"areaPoints\" ~= '{$points}'");
    }

    /* Проверяет, если они пересекаются, а не просто касаются.
        Плохо работает для не квадратов
    */
    public static function whereRawIntersects(string $points)
    {
//        return self::whereRaw("pclose(path(\"areaPoints\")) ??# pclose(path('{$points}'))");
        return self::whereRaw("
                PATH(\"areaPoints\") <-> PATH('{$points}') = 0  AND
                POLYGON(\"areaPoints\") && POLYGON('{$points}') AND
                (POLYGON(\"areaPoints\") @> POLYGON('{$points}') <> TRUE OR
                POLYGON(\"areaPoints\") <@ POLYGON('{$points}') <> TRUE) AND
                AREA(BOX(POLYGON(\"areaPoints\")) # BOX(POLYGON('{$points}'))) <> 0
        ");
    }

    public static function hasIntersectingTriangles(string $pointsString, $id = null)
    {
        $points = self::convertStringToPoints($pointsString);
        if (count($points) < 3) return false;

        $areas = self::whereRawIntersects($pointsString)->get();
        foreach ($areas as $area) {
            if ($id == $area->id) continue;

            $pointsFound = self::convertStringToPoints($area->areaPoints);
            $uniquePoints = [];
            $samePoints = 0;

            foreach ($points as $point1) {
                $match = false;
                foreach ($pointsFound as $point2) {
                    if ($point1["longitude"] == $point2["longitude"] && $point1["latitude"] == $point2["latitude"]) {
                        $match = true;
                        $samePoints++;
                        break;
                    }
                }
                if (!$match) {
                    $uniquePoints[] = $point1;
                }
            }
            if (count($uniquePoints) == 0) return true;

            $uniquePoints = self::convertManyPointsToString($uniquePoints);
            if ($area = self::whereRaw("path(\"areaPoints\") <-> path('$uniquePoints') = 0")
                ->where(['id' => $area->id])->first())
                return $area;

            if ($samePoints == 0)
                return true;
        }

        return false;
    }
}
