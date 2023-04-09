<?php

namespace Api\Controllers\Api\AnimalType;

use Api\Core\Http\BaseController;
use Api\Core\Models\Account;
use Api\Core\Models\Animal;
use Api\Core\Models\AnimalLocation;
use Api\Core\Services\AnimalDataFormatter;
use Api\Core\Services\Authorization;
use Api\Core\Services\Formatters\AnimalFormatter;
use Api\Core\Services\Formatters\DateFormatter;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Api\Core\Factories\ResponseFactory;
use Api\Core\Models\Location;
use Api\Core\Models\Type;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Api\Core\Validation\Constraints as OwnAssert;

class AddController extends BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $currentAccount = Authorization::getCurrentAccount();
        if ($currentAccount && !$currentAccount->isAdmin() && !$currentAccount->isChipper())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_FORBIDDEN);

        $animalId = $args['animalId'];
        $typeId = $args['typeId'];

        $animal = Animal::find($animalId);
        if (!$animal)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        if (!Type::find($typeId))
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        if ($animal->types()->find($typeId))
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_CONFLICT);

        $animal->types()->attach($typeId);
        return ResponseFactory::MakeJSON(AnimalFormatter::prepareOne($animal))
            ->Custom(StatusCodeInterface::STATUS_CREATED);
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $animalId = $args['animalId'];
        $errors = $this->validate($animalId,
            [new Assert\NotNull(), new Assert\Positive()]);
        if ($errors) return $errors;

        $typeId = $args['typeId'];
        return $this->validate($typeId,
            [new Assert\NotNull(), new Assert\Positive()]);
    }
}