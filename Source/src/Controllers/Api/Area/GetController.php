<?php

namespace Api\Controllers\Api\Area;

use Api\Core\Http\BaseController;
use Api\Core\Models\Area;
use Api\Core\Services\Authorization;
use Api\Core\Services\Formatters\AreaFormatter;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Api\Core\Factories\ResponseFactory;
use Symfony\Component\Validator\Constraints as Assert;
use Api\Core\Validation\Constraints as OwnAssert;
use Symfony\Component\Validator\Constraints\NotBlank;

class GetController extends BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $areaId = (int)$args['areaId'];

        $area = Area::find($areaId);
        if (!$area)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        return ResponseFactory::MakeJSON(AreaFormatter::PrepareOne($area))->Success();
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $areaId = (int)$args['areaId'];
        return $this->validate($areaId, [new Assert\NotNull(), new Assert\Positive()]);
    }
}