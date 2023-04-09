<?php

namespace Api\Controllers\Api\Animal;

use Api\Core\Http\BaseController;
use Api\Core\Models\Account;
use Api\Core\Models\Animal;
use Api\Core\Services\Authorization;
use Api\Core\Services\Formatters\AnimalFormatter;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Api\Core\Factories\ResponseFactory;
use Api\Core\Models\Location;
use Api\Core\Models\Type;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Api\Core\Validation\Constraints as OwnAssert;

class DeleteController extends BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $currentAccount = Authorization::getCurrentAccount();
        if ($currentAccount && !$currentAccount->isAdmin())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_FORBIDDEN);

        $animalId = $args['animalId'];
        $animal = Animal::find($animalId);
        if (!$animal)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        if ($animal->locations()->first())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_BAD_REQUEST);

        if ($animal->delete())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_OK);

        return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $animalId = $args['animalId'];
        return $this->validate($animalId,
            [new Assert\NotBlank(), new Assert\Positive()]);
    }
}