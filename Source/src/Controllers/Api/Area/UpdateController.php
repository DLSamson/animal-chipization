<?php

namespace Api\Controllers\Api\Area;

use Api\Core\Http\BaseController;
use Api\Core\Models\Area;
use Api\Core\Services\Authorization;
use Api\Core\Services\Formatters\AreaFormatter;
use Api\Core\Services\Geometry;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Api\Core\Factories\ResponseFactory;
use Symfony\Component\Validator\Constraints as Assert;
use Api\Core\Validation\Constraints as OwnAssert;
use Symfony\Component\Validator\Constraints\NotBlank;

class UpdateController extends BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $currentAccount = Authorization::getCurrentAccount();
        if ($currentAccount && !$currentAccount->isAdmin())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_FORBIDDEN);

        $areaId = $args['areaId'];
        $json = $request->getBody();
        $data = json_decode($json, true);
        $pointsCount = count($data['areaPoints']);
        $data['areaPoints'] = Area::convertManyPointsToString($data['areaPoints']);

        if (Area::where([['id', '<>', $areaId], 'name' => $data['name']])->first())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_CONFLICT,
                'Name has already been taken');

        if ($secondArea = Area::whereRawRepeat($data['areaPoints'])->where([['id', '<>', $areaId]])->first())
            return ResponseFactory::MakeJSON($secondArea->toArray())->Custom(StatusCodeInterface::STATUS_CONFLICT,
                'Points have already been taken');

        if ($pointsCount != 3) {
            if ($secondArea = Area::whereRawIntersects($data['areaPoints'])->where([['id', '<>', $areaId]])->first())
                return ResponseFactory::MakeJSON($secondArea->toArray())->Custom(StatusCodeInterface::STATUS_BAD_REQUEST,
                    'The zone intersects with another zone');
        } else
            if ($area = Area::hasIntersectingTriangles($data['areaPoints'], $areaId))
                return ResponseFactory::MakeJSON($area->toArray())->Custom(StatusCodeInterface::STATUS_BAD_REQUEST,
                    'The zone intersects with another zone');

        $area = Area::find($areaId);
        $area->fill($data);

        if ($area->save())
            return ResponseFactory::MakeJSON(AreaFormatter::PrepareOne($area))->Success();

        return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $areaId = (int)$args['areaId'];
        $errors = $this->validate($areaId, [new Assert\NotNull(), new Assert\Positive()]);
        if ($errors) return $errors;

        $json = $request->getBody();
        $data = json_decode($json, true);
        $errors = $this->validate($data, new Assert\Collection([
            'name' => [new NotBlank(), new OwnAssert\NotEmptyString()],
            'areaPoints' => [
                new Assert\All(new Assert\Collection([
                    'latitude' => [new Assert\NotNull(), new Assert\Range(['min' => -90, 'max' => 90])],
                    'longitude' => [new Assert\NotNull(), new Assert\Range(['min' => -180, 'max' => 180])]
                ])),
                new Assert\Count(['min' => 3]),
            ],
        ]));
        if ($errors) return $errors;

        /* Check if points are the same */
        foreach ($data['areaPoints'] as $point) {
            foreach ($data['areaPoints'] as $point2)
                if ($point['latitude'] == $point2['latitude'] && $point['longitude'] == $point2['longitude'] && $point != $point2)
                    return ['areaPoints' => 'Area points must not have the same points'];
        }

        if (Geometry::isPolygonSelfIntersecting($data['areaPoints']))
            return ['areaPoints' => 'Area polygons cannot intersect themselves'];

        /* @TODO Проверка на принадлженость точек одной линии */

        return $errors;
    }
}