<?php

namespace Api\Core\Services;

class Geometry
{
    public static function isPolygonSelfIntersecting(array $locationPoints)
    {
        if (count($locationPoints) <= 3) {
            return false;
        }
        $points = array_map(fn($el) => self::convertCoordinateToPoint($el), $locationPoints);

        $lines = [];
        for ($i = 0; $i < count($points); $i++) {
            if ($i != count($points) - 1) {
                $lines[] = self::getLine($points[$i], $points[$i + 1]);
            } else {
                $lines[] = self::getLine($points[$i], $points[0]);
            }
        }

        for ($i = 0; $i < count($lines); $i++) {
            $result = self::isLinesIntersecting($lines[$i], $lines[($i + 2) % count($lines)]);
            if ($result) return true;
        }

        return false;
    }

    public static function getLine($pointA, $pointB)
    {
        return [
            'begin' => [
                'x' => $pointA['x'],
                'y' => $pointA['y'],
            ],
            'end' => [
                'x' => $pointB['x'],
                'y' => $pointB['y'],
            ]
        ];
    }

    public static function convertCoordinateToPoint(array $locationPoint)
    {
        return [
            'x' => $locationPoint['longitude'],
            'y' => $locationPoint['latitude']
        ];
    }

    public static function isLinesIntersecting($lineA, $lineB)
    {
        $x1 = $lineA['begin']['x'];
        $y1 = $lineA['begin']['y'];
        $x2 = $lineA['end']['x'];
        $y2 = $lineA['end']['y'];
        $x3 = $lineB['begin']['x'];
        $y3 = $lineB['begin']['y'];
        $x4 = $lineB['end']['x'];
        $y4 = $lineB['end']['y'];

        $denominator = ($y4 - $y3) * ($x1 - $x2) - ($x4 - $x3) * ($y1 - $y2);

        if ($denominator == 0) {
            return ($x1 * $y2 - $x2 * $y1) * ($x4 - $x3) - ($x3 * $y4 - $x4 * $y3) * ($x2 - $x1) == 0 && ($x1 * $y2 - $x2 * $y1) * ($y4 - $y3) - ($x3 * $y4 - $x4 * $y3) * ($y2 - $y1) == 0;
        } else {
            $numerator_a = ($x4 - $x2) * ($y4 - $y3) - ($x4 - $x3) * ($y4 - $y2);
            $numerator_b = ($x1 - $x2) * ($y4 - $y2) - ($x4 - $x2) * ($y1 - $y2);
            $Ua = $numerator_a / $denominator;
            $Ub = $numerator_b / $denominator;
            return $Ua >= 0 && $Ua <= 1 && $Ub >= 0 && $Ub <= 1;
        }
    }
}