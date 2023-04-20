<?php

namespace Api\Controllers\Api;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EchoController extends BaseController
{

    public function process(Request $request, Response $response, array $args = []): Response
    {
        return ResponseFactory::MakeJSON($args['value'])->Success();
    }
}