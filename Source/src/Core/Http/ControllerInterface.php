<?php

namespace Api\Core\Http;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

interface ControllerInterface {

    public function handle(Request $request, Response $response, array $args = []) : Response;
}