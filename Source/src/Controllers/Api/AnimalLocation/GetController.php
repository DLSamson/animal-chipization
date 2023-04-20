<?php

namespace Api\Controllers\Api\AnimalLocation;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Animal;
use Api\Core\Models\AnimalLocation;
use Api\Core\Services\Formatters\AnimalLocationFormatter;
use Api\Core\Validation\Constraints as OwnAssert;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;

class GetController extends BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $animalId = $args['animalId'];

        $params = $request->getQueryParams();
        $params['from'] = $params['from'] ?: 0;
        $params['size'] = $params['size'] ?: 10;

        $queryCondition = [];
        if ($params['startDateTime'])
            $queryCondition[] = ['dateTimeOfVisitLocationPoint', '>=', $params['startDateTime']];
        if ($params['endDateTime'])
            $queryCondition[] = ['dateTimeOfVisitLocationPoint', '<=', $params['endDateTime']];

        $animal = Animal::find($animalId);
        if (!$animal) return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        $locations = AnimalLocation::where($queryCondition)
            ->where(['animal_id' => $animal->id])
            ->orderBy('dateTimeOfVisitLocationPoint', 'ASC')
            ->offset($params['from'])
            ->limit($params['size'])
            ->get();

        return ResponseFactory::MakeJSON(AnimalLocationFormatter::PrepareMany($locations))->Success();
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $animalId = $args['animalId'];
        $errors = $this->validate($animalId, new Assert\Positive());
        if ($errors) return $errors;

        $params = $request->getQueryParams();
        $params['from'] = $params['from'] ?: 0;
        $params['size'] = $params['size'] ?: 10;

        return $this->validate($params, new Assert\Collection([
            'from' => new Assert\Required([new Assert\NotBlank(), new Assert\PositiveOrZero()]),
            'size' => new Assert\Required([new Assert\NotBlank(), new Assert\Positive()]),
            'startDateTime' => new Assert\Optional(new OwnAssert\DateTimeInISO_8601()),
            'endDateTime' => new Assert\Optional(new OwnAssert\DateTimeInISO_8601()),
        ]));
    }
}