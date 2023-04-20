<?php

namespace Api\Controllers\Api\Location;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Location;
use Api\Core\Services\Authorization;
use Api\Core\Services\Formatters\LocationFormatter;
use Api\Core\Services\LocationJSONfixer;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateController extends BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $currentAccount = Authorization::getCurrentAccount();
        if ($currentAccount && !$currentAccount->isAdmin() && !$currentAccount->isChipper())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_FORBIDDEN);

        $pointId = $args['pointId'];
        $json = $request->getBody();
        $data = json_decode(LocationJSONfixer::fix($json), true);

        $queryCondition = array_merge($data, [['id', '<>', $pointId]]);
        if (Location::where($queryCondition)->first())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_CONFLICT);

        $location = Location::find($pointId);
        if (!$location)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        if ($location->chippedAnimals()->first())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_BAD_REQUEST);

        $location->fill($data);
        if ($location->save())
            return ResponseFactory::MakeJSON(LocationFormatter::PrepareOne($location))->Success();

        return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $pointId = $args['pointId'];
        $errors = $this->validate($pointId, [new Assert\NotNull(), new Assert\Positive()]);
        if ($errors) return $errors;

        $json = $request->getBody();
        $data = json_decode($json, true);
        return $this->validate($data, new Assert\Collection([
            'latitude' => [new Assert\NotNull(), new Assert\Range(['min' => -90, 'max' => 90])],
            'longitude' => [new Assert\NotNull(), new Assert\Range(['min' => -180, 'max' => 180])]
        ]));
    }
}