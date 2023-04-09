<?php

namespace Api\Controllers\Api\Type;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Account;
use Api\Core\Models\Role;
use Api\Core\Models\Type;
use Api\Core\Services\Authorization;
use Api\Core\Services\Formatters\AccountFormatter;
use Api\Core\Services\Formatters\TypeFormatter;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;
use Api\Core\Validation\Constraints as OwnAssert;

class CreateController extends BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $currentAccount = Authorization::getCurrentAccount();
        if ($currentAccount && (!$currentAccount->isAdmin() && !$currentAccount->isChipper()))
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_FORBIDDEN);

        $json = $request->getBody();
        $data = json_decode($json, true);

        if (Type::where($data)->first())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_CONFLICT);

        $type = new Type($data);
        if ($type->save())
            return ResponseFactory::MakeJSON(TypeFormatter::PrepareOne($type))
                ->Custom(StatusCodeInterface::STATUS_CREATED);

        return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $json = $request->getBody();
        $data = json_decode($json, true);
        return $this->validate($data, new Assert\Collection([
            'type' => [new Assert\NotBlank(), new OwnAssert\NotEmptyString()],
        ]));
    }
}