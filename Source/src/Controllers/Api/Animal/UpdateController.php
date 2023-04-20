<?php

namespace Api\Controllers\Api\Animal;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Account;
use Api\Core\Models\Animal;
use Api\Core\Models\AnimalLocation;
use Api\Core\Models\Location;
use Api\Core\Services\Authorization;
use Api\Core\Services\Formatters\AnimalFormatter;
use Api\Core\Services\Formatters\DateFormatter;
use Fig\Http\Message\StatusCodeInterface;
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

        if ($animal->lifeStatus == Animal::LIFE_STATUS_DEAD && $data['lifeStatus'] == Animal::LIFE_STATUS_ALIVE)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_BAD_REQUEST);

        $visitedLocation = AnimalLocation::where(['animal_id' => $animalId])->first();
        if ($visitedLocation != null && $visitedLocation->location_id == $data['chippingLocationId'])
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_BAD_REQUEST);

        if (!Location::where(['id' => $data['chippingLocationId']])->first())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        if (!Account::find($data['chipperId']))
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        $animal->fill($data);
        $animal->deathDateTime = $data['lifeStatus'] == Animal::LIFE_STATUS_DEAD
            ? DateFormatter::formatToISO8601('now')
            : null;

        if ($animal->save())
            return ResponseFactory::MakeJSON(AnimalFormatter::PrepareOne($animal))->Success();

        return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $animalId = $args['animalId'];
        $errors = $this->validate($animalId,
            [new Assert\NotBlank(), new Assert\Positive()]);
        if ($errors) return $errors;

        $json = $request->getBody();
        $data = json_decode($json, true);
        return $this->validate($data, new Assert\Collection([
            "weight" => [new Assert\NotNull(), new Assert\Positive()],
            "length" => [new Assert\NotNull(), new Assert\Positive()],
            "height" => [new Assert\NotNull(), new Assert\Positive()],
            "gender" => [new Assert\NotBlank(), new Assert\Choice(Animal::genderValues())],
            "lifeStatus" => [new Assert\NotBlank(), new Assert\Choice(Animal::lifeStatusValues())],
            "chipperId" => [new Assert\NotNull(), new Assert\Positive()],
            "chippingLocationId" => [new Assert\NotNull(), new Assert\Positive()],
        ]));
    }
}