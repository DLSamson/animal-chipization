<?php

namespace Api\Controllers\Api\AnimalLocation;

use Api\Core\Http\BaseController;
use Api\Core\Models\Animal;
use Api\Core\Models\AnimalLocation;
use Api\Core\Services\Authorization;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Api\Core\Factories\ResponseFactory;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class DeleteController extends BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $currentAccount = Authorization::getCurrentAccount();
        if ($currentAccount && !$currentAccount->isAdmin())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_FORBIDDEN);

        $animalId = $args['animalId'];
        $visitedPointId = $args['visitedPointId'];

        $animal = Animal::find($animalId);
        if (!$animal)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        /* @var Collection $visited */
        $visited = AnimalLocation::where(['animal_id' => $animalId])
            ->orderBy('dateTimeOfVisitLocationPoint')->get();
        if (!$visited->find($visitedPointId))
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        $visitedPoint = AnimalLocation::where([
            'animal_id' => $animalId, 'id' => $visitedPointId])->first();
        if (!$visitedPoint)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        $visitedPointIndex = $visited->search(fn($el) => $el->id === $visitedPointId);
        $visitedPoint->delete();
        if ($visitedPointIndex != $visited->count() - 1 &&
            $visited->get($visitedPointIndex + 1)->location_id == $animal->chippingLocationId)
            $visited->get($visitedPointIndex + 1)->delete();

        return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_OK);
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $animalId = $args['animalId'];
        $errors = $this->validate($animalId, new Assert\Positive());
        if ($errors) return $errors;

        $visitedPointId = $args['visitedPointId'];
        return $this->validate($visitedPointId, new Assert\Positive());
    }
}