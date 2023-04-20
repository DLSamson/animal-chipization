<?php

namespace Api\Controllers\Api\AnimalLocation;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Animal;
use Api\Core\Models\AnimalLocation;
use Api\Core\Models\Location;
use Api\Core\Services\Authorization;
use Api\Core\Services\Formatters\AnimalLocationFormatter;
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
        if ($currentAccount && !$currentAccount->isChipper() && !$currentAccount->isAdmin())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_FORBIDDEN);

        $animalId = $args['animalId'];
        $json = $request->getBody();
        $data = json_decode($json, true);

        $animal = Animal::find($animalId);
        if (!$animal)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        $location = Location::find($data['locationPointId']);
        if (!$location)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        /* @var Collection $visitedLocations */
        $visitedLocations = AnimalLocation::where([
            'animal_id' => $animalId,
        ])->get()->sortBy('dateTimeOfVisitLocationPoint');
        if (!$visitedLocations->find($data['visitedLocationPointId']))
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        if ($visitedLocations->first()->id == $data['visitedLocationPointId'] &&
            $data['locationPointId'] == $animal->chippingLocationId
        )
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_BAD_REQUEST);

        $visitedLocationToUpdate = $visitedLocations->find($data['visitedLocationPointId']);
        if ($visitedLocationToUpdate->location_id == $location->id)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_BAD_REQUEST);

        /* Проверяем, что предыдущая и последующая точки посещения не равны между собой */
        $visitedLocationToUpdateIndex = $visitedLocations->search(fn($el) => $el->id == $visitedLocationToUpdate->id);
        if ($visitedLocationToUpdateIndex != 0)
            if ($visitedLocations->get($visitedLocationToUpdateIndex - 1)->location_id == $location->id)
                return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_BAD_REQUEST);
        if ($visitedLocationToUpdateIndex != $visitedLocations->count() - 1)
            if ($visitedLocations->get($visitedLocationToUpdateIndex + 1)->location_id == $location->id)
                return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_BAD_REQUEST);

        $visitedLocationToUpdate->location_id = $location->id;
        if ($visitedLocationToUpdate->save())
            return ResponseFactory::MakeJSON(AnimalLocationFormatter::PrepareOne($visitedLocationToUpdate))
                ->Success();
        return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $animalId = $args['animalId'];
        $errors = $this->validate($animalId, new Assert\Positive());
        if ($errors) return $errors;

        $json = $request->getBody();
        $data = json_decode($json, true);
        return $this->validate($data, new Assert\Collection([
            'visitedLocationPointId' => [new Assert\NotNull(), new Assert\Positive()],
            'locationPointId' => [new Assert\NotNull(), new Assert\Positive()],
        ]));
    }
}