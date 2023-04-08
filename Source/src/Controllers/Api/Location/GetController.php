<?php

namespace Api\Controllers\Api\Location;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Services\Formatters\LocationFormatter;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;
use Api\Core\Models\Location;

class GetController extends \Api\Core\Http\BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $pointId = $args['pointId'];
        $location = Location::find($pointId);
        if (!$location)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        return ResponseFactory::MakeJSON(LocationFormatter::PrepareOne($location))->Success();
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $pointId = $args['pointId'];
        return $this->validate($pointId, new Assert\Positive());
    }
}