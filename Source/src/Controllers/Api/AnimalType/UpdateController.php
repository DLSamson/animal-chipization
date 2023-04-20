<?php

namespace Api\Controllers\Api\AnimalType;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Animal;
use Api\Core\Models\Type;
use Api\Core\Services\Authorization;
use Api\Core\Services\Formatters\AnimalFormatter;
use Fig\Http\Message\StatusCodeInterface;
use Illuminate\Database\Eloquent\Collection;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateController extends BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $currentAccount = Authorization::getCurrentAccount();
        if ($currentAccount && !$currentAccount->isAdmin() && !$currentAccount->isChipper())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_FORBIDDEN);

        $animalId = $args['animalId'];
        $json = $request->getBody();
        $data = json_decode($json, true);

        $animal = Animal::find($animalId);
        if (!$animal)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        if (!Type::find($data['oldTypeId']))
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        if (!Type::find($data['newTypeId']))
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        /* @var Collection $animalTypes */
        $animalTypes = $animal->types()->get();

        if (!$animalTypes->find($data['oldTypeId']))
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);
        if ($animalTypes->find($data['newTypeId']))
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_CONFLICT);
        if ($animalTypes->find($data['newTypeId']) && $animalTypes->find($data['oldTypeId']))
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_CONFLICT);

        $animal->types()->detach($data['oldTypeId']);
        $animal->types()->attach($data['newTypeId']);

        return ResponseFactory::MakeJSON(AnimalFormatter::prepareOne($animal))->Success();
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $animalId = $args['animalId'];
        $errors = $this->validate($animalId,
            [new Assert\NotNull(), new Assert\Positive()]);
        if ($errors) return $errors;

        $json = $request->getBody();
        $data = json_decode($json, true);
        return $this->validate($data, new Assert\Collection([
            'oldTypeId' => [new Assert\NotNull(), new Assert\Positive()],
            'newTypeId' => [new Assert\NotNull(), new Assert\Positive()],
        ]));
    }
}