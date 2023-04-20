<?php

namespace Api\Controllers\Api\Type;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Type;
use Api\Core\Services\Authorization;
use Api\Core\Services\Formatters\TypeFormatter;
use Api\Core\Validation\Constraints as OwnAssert;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateController extends BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $currentAccount = Authorization::getCurrentAccount();
        if ($currentAccount && (!$currentAccount->isAdmin() && !$currentAccount->isChipper()))
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_FORBIDDEN);

        $typeId = $args['typeId'];
        $json = $request->getBody();
        $data = json_decode($json, true);

        $queryCondition = array_merge($data, [['id', '<>', $typeId]]);
        if (Type::where($queryCondition)->first())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_CONFLICT);

        $type = Type::find($typeId);
        if (!$type)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        $type->fill($data);
        if ($type->save())
            return ResponseFactory::MakeJSON(TypeFormatter::PrepareOne($type))->Success();

        return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $typeId = $args['typeId'];
        $errors = $this->validate($typeId, [new Assert\NotNull(), new Assert\Positive()]);
        if ($errors) return $errors;

        $json = $request->getBody();
        $data = json_decode($json, true);
        $errors = $this->validate($data, new Assert\Collection([
            'type' => [new Assert\NotBlank(), new OwnAssert\NotEmptyString()],
        ]));
        return $errors;
    }
}