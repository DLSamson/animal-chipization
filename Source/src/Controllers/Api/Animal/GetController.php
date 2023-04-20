<?php

namespace Api\Controllers\Api\Animal;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Animal;
use Api\Core\Services\Formatters\AnimalFormatter;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;

class GetController extends BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $animalId = $args['animalId'];

        $animal = Animal::find($animalId);
        if (!$animal)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        return ResponseFactory::MakeJSON(AnimalFormatter::PrepareOne($animal))->Success();
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $animalId = $args['animalId'];
        return $this->validate($animalId,
            [new Assert\NotBlank(), new Assert\Positive()]);
    }
}