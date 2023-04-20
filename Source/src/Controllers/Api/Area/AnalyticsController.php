<?php

namespace Api\Controllers\Api\Area;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Area;
use Api\Core\Services\Analytics;
use Api\Core\Validation\Constraints as OwnAssert;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;

class AnalyticsController extends BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $areaId = (int)$args['areaId'];
        $params = $request->getQueryParams();

        $area = Area::find($areaId);
        if (!$area)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        $analytics = Analytics::GetAreaAnalytics($area, $params['startDate'], $params['endDate']);

        return ResponseFactory::MakeJSON($analytics)->Success();
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $areaId = (int)$args['areaId'];
        $errors = $this->validate($areaId, [new Assert\NotNull(), new Assert\Positive()]);
        if ($errors) return $errors;

        $params = $request->getQueryParams();
        return $this->validate($params, new Assert\Collection([
            'startDate' => new OwnAssert\DateTimeInISO_8601(),
            'endDate' => [
                new OwnAssert\DateTimeInISO_8601(),
                new Assert\GreaterThan($params['startDate']),
            ],
        ]));
    }
}