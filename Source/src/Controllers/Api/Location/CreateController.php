<?php

namespace Api\Controllers\Api\Location;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Location;
use Api\Core\Services\Formatters\LocationFormatter;
use Api\Core\Services\LocationJSONfixer;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;

class CreateController extends BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $json = $request->getBody();
        $data = json_decode(LocationJSONfixer::fix($json), true);

        if (Location::where($data)->first())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_CONFLICT);

        $location = new Location($data);
        if ($location->save())
            return ResponseFactory::MakeJSON(LocationFormatter::PrepareOne($location))
                ->Custom(StatusCodeInterface::STATUS_CREATED);

        return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $json = $request->getBody();
        $data = json_decode($json, true, JSON_BIGINT_AS_STRING);
        return $this->validate($data, new Assert\Collection([
            'latitude' => [new Assert\NotNull(), new Assert\Range(['min' => -90, 'max' => 90])],
            'longitude' => [new Assert\NotNull(), new Assert\Range(['min' => -180, 'max' => 180])]
        ]));
    }
}