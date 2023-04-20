<?php

namespace Api\Core\Http;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

interface ControllerInterface
{

    public function handle(Request $request, Response $response, array $args = []): Response;
}