<?php

namespace Api\Controllers\Api\Account;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Account;
use Api\Core\Services\Authorization;
use Api\Core\Services\Formatters\AccountFormatter;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;

class GetController extends BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $currentAccount = Authorization::getCurrentAccount();
        if ($currentAccount && !$currentAccount->isAdmin() && $currentAccount->id != $args['accountId'])
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_FORBIDDEN);

        $accountId = $args['accountId'];
        $account = Account::find($accountId);

        if (!$account) return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        return ResponseFactory::MakeJSON(AccountFormatter::PrepareOne($account))->Success();
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $accountId = $args['accountId'];
        return $this->validate($accountId, [
            new Assert\NotNull,
            new Assert\Positive,
        ]);
    }
}