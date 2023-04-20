<?php

namespace Api\Controllers\Api\Animal;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Animal;
use Api\Core\Services\Formatters\AnimalFormatter;
use Api\Core\Validation\Constraints as OwnAssert;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;

class SearchController extends BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $params = $request->getQueryParams();
        $params['from'] = $params['from'] ?: 0;
        $params['size'] = $params['size'] ?: 10;

        $queryConditions = array_filter($params,
            fn($el) => !in_array($el, ['from', 'size', 'startDateTime', 'endDateTime']), ARRAY_FILTER_USE_KEY);

        if ($params['startDateTime'])
            $queryCondition[] = ['dateTimeOfVisitLocationPoint', '>=', $params['startDateTime']];
        if ($params['startDateTime'])
            $queryCondition[] = ['dateTimeOfVisitLocationPoint', '<=', $params['endDateTime']];

        $animals = Animal::where($queryConditions)
            ->orderBy('id')
            ->limit($params['size'])
            ->offset($params['from'])
            ->get();

        return ResponseFactory::MakeJSON(AnimalFormatter::PrepareMany($animals))->Success();
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $params = $request->getQueryParams();
        $params['from'] = $params['from'] ?: 0;
        $params['size'] = $params['size'] ?: 10;

        return $this->validate($params, new Assert\Collection([
            'from' => new Assert\Required([
                new Assert\NotBlank(),
                new Assert\PositiveOrZero(),
            ]),
            'size' => new Assert\Required([
                new Assert\NotBlank(),
                new Assert\Positive(),
            ]),
            'startDateTime' => new Assert\Optional(new OwnAssert\DateTimeInISO_8601()),
            'endDateTime' => new Assert\Optional(new OwnAssert\DateTimeInISO_8601()),
            'chipperId' => new Assert\Optional(new Assert\Positive()),
            'chippingLocationId' => new Assert\Optional(new Assert\Positive()),
            'lifeStatus' => new Assert\Optional(new Assert\Choice([], ['ALIVE', 'DEAD'])),
            'gender' => new Assert\Optional(new Assert\Choice([], ['MALE', 'FEMALE', 'OTHER'])),
        ]));
    }
}