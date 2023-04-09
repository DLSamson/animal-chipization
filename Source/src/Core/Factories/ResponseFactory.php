<?php

namespace Api\Core\Factories;

use Slim\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Fig\Http\Message\StatusCodeInterface;

class ResponseFactory
{
    public Response $response;

    public static function MakeJSON($data = null): ResponseFactory
    {
        $response = new Response();
        $response->getBody()->write(json_encode($data ?: []));

        $responseFactory = new static;
        $responseFactory->response = $response
            ->withHeader('content-type', 'application/json');
        return $responseFactory;
    }

    public static function Make($content = ''): ResponseFactory
    {
        $response = new Response();
        $response->getBody()->write($content);

        $responseFactory = new static;
        $responseFactory->response = $response
            ->withHeader('content-type', 'text/html');
        return $responseFactory;
    }

    public static function MakeFromStatusCode(int $statusCode, string $reasonPhrase = ''): Response
    {
        return (new Response())->withStatus($statusCode, $reasonPhrase);
    }

    public function Custom(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        return $this->response
            ->withStatus($code, $reasonPhrase);
    }

    public function Success(): ResponseInterface
    {
        return $this->response
            ->withStatus(StatusCodeInterface::STATUS_OK);
    }

    public function BadRequest(): ResponseInterface
    {
        return $this->response
            ->withStatus(StatusCodeInterface::STATUS_BAD_REQUEST);
    }
}