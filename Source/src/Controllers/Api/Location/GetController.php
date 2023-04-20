<?php

namespace Api\Controllers\Api\Location;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Models\Location;
use Api\Core\Services\Formatters\LocationFormatter;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Optional;

class GetController extends \Api\Core\Http\BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $params = $request->getQueryParams();
        if ($params) {
            $location = Location::where($params)->first();
            if ($location)
                return ResponseFactory::MakeJSON(LocationFormatter::PrepareOne($location))->Success();
        }

        $pointId = $args['pointId'];
        $location = Location::find($pointId);
        if (!$location)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        return ResponseFactory::MakeJSON(LocationFormatter::PrepareOne($location))->Success();
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $pointId = $args['pointId'];
        $errors = $this->validate($pointId, new Assert\Optional(new Assert\Positive()));
        if ($errors) return $errors;

        $params = $request->getQueryParams();
        return $this->validate($params, new Assert\Collection([
            'latitude' => new Optional([new Assert\NotBlank(), new Assert\NotNull()]),
            'longitude' => new Optional([new Assert\NotBlank(), new Assert\NotNull()]),
        ]));
    }
}