<?php

namespace Api\Controllers\Api\AnimalLocation;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Animal;
use Api\Core\Models\AnimalLocation;
use Api\Core\Models\Location;
use Api\Core\Services\Authorization;
use Api\Core\Services\Formatters\AnimalLocationFormatter;
use Api\Core\Services\Formatters\DateFormatter;
use Fig\Http\Message\StatusCodeInterface;
use Illuminate\Database\Eloquent\Collection;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;

class AddController extends BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $currentAccount = Authorization::getCurrentAccount();
        if ($currentAccount && !$currentAccount->isChipper() && !$currentAccount->isAdmin())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_FORBIDDEN);

        $animalId = $args['animalId'];
        $pointId = $args['pointId'];

        $animal = Animal::find($animalId);
        if (!$animal) return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        if (!Location::find($pointId))
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        if ($animal->lifeStatus === 'DEAD')
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_BAD_REQUEST);

        /* @var Collection $visited */
        $visited = AnimalLocation::where(['animal_id' => $animalId])->get();
        if ($visited->count() === 0 && $pointId == $animal->chippingLocationId)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_BAD_REQUEST);

        if ($visited->last()->location_id == $pointId)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_BAD_REQUEST);

        $newVisited = new AnimalLocation([
            'animal_id' => $animalId,
            'location_id' => $pointId,
            'dateTimeOfVisitLocationPoint' => DateFormatter::formatToISO8601('now'),
        ]);
        if ($newVisited->save())
            return ResponseFactory::MakeJSON(AnimalLocationFormatter::PrepareOne($newVisited))
                ->Custom(StatusCodeInterface::STATUS_CREATED);

        return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $animalId = $args['animalId'];
        $errors = $this->validate($animalId, new Assert\Positive());
        if ($errors) return $errors;

        $pointId = $args['pointId'];
        return $this->validate($pointId, new Assert\Positive());
    }
}