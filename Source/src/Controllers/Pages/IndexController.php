<?php

namespace Api\Controllers\Pages;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\RenderController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class IndexController extends RenderController
{

    public function process(Request $request, Response $response, array $args = []): Response
    {
        return ResponseFactory::Make($this->render('layout', [
            'title' => 'Slim Web API template',
            'message' => 'Welcome!'
        ]))->Success();
    }
}