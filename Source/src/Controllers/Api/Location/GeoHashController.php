<?php

namespace Api\Controllers\Api\Location;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;

class GeoHashController extends BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $params = $request->getQueryParams();
        $data = array_merge($params, $args);
        return ResponseFactory::MakeJSON($data)->Success();
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $params = $request->getQueryParams();
        return $this->validate($params, new Assert\Collection([
            'latitude' => [new Assert\NotNull(), new Assert\Range(['min' => -90, 'max' => 90])],
            'longitude' => [new Assert\NotNull(), new Assert\Range(['min' => -180, 'max' => 180])]
        ]));
    }
}