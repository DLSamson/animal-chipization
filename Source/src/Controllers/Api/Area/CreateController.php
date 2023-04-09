<?php

namespace Api\Controllers\Api\Area;

use Api\Core\Http\BaseController;
use Api\Core\Models\Area;
use Api\Core\Services\Authorization;
use Api\Core\Services\Formatters\AreaFormatter;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Api\Core\Factories\ResponseFactory;
use Symfony\Component\Validator\Constraints as Assert;
use Api\Core\Validation\Constraints as OwnAssert;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreateController extends BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $currentAccount = Authorization::getCurrentAccount();
        if ($currentAccount && !$currentAccount->isAdmin())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_FORBIDDEN);

        $json = $request->getBody();
        $data = json_decode($json, true);


        /* @TODO Добавить прочие проверки на пересечение */
        if (!Area::where([['areaPoints', '&&', Area::convertManyPointsToString($data['areaPoints'])]])->get())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_BAD_REQUEST);

        $data['areaPoints'] = Area::convertManyPointsToString($data['areaPoints']);
        $area = new Area($data);

        if ($area->save())
            return ResponseFactory::MakeJSON(AreaFormatter::PrepareOne($area))
                ->Custom(StatusCodeInterface::STATUS_CREATED);

        return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
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

        /* @TODO Проверка на самопересечение */
        /* @TODO Проверка на принадлженость точек одной линии */

        return $errors;
    }
}