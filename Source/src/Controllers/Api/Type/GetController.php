<?php

namespace Api\Controllers\Api\Type;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Account;
use Api\Core\Models\Role;
use Api\Core\Models\Type;
use Api\Core\Services\Formatters\AccountFormatter;
use Api\Core\Services\Formatters\TypeFormatter;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;
use Api\Core\Validation\Constraints as OwnAssert;

class GetController extends BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $typeId = $args['typeId'];
        $type = Type::find($typeId);

        if (!$type)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        return ResponseFactory::MakeJSON(TypeFormatter::PrepareOne($type))->Success();
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $typeId = $args['typeId'];
        return $this->validate($typeId, new Assert\Positive());
    }
}