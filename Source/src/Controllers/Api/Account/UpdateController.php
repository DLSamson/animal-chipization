<?php

namespace Api\Controllers\Api\Account;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Account;
use Api\Core\Models\Role;
use Api\Core\Services\Authorization;
use Api\Core\Services\Formatters\AccountFormatter;
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
        if ($currentAccount && !$currentAccount->isAdmin() && $currentAccount->id != $args['accountId'])
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_FORBIDDEN);

        $accountId = $args['accountId'];
        $json = $request->getBody();
        $data = json_decode($json, true);
        $data['password'] = Account::HashPassword($data['password']);

        if (Account::where(['email' => $data['email'], ['id', '<>', $accountId]])->first())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_CONFLICT);

        $account = Account::find($accountId);
        if (!$account)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);

        $account->fill($data);
        if ($account->save())
            return ResponseFactory::MakeJSON(AccountFormatter::PrepareOne($account))->Success();

        return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $accountId = $args['accountId'];
        $errors = $this->validate($accountId, [
            new Assert\NotNull,
            new Assert\Positive,
        ]);
        if ($errors) return $errors;

        $json = $request->getBody();
        $data = json_decode($json, true);

        return $this->validate($data, new Assert\Collection([
            'firstName' => [new Assert\NotBlank(), new OwnAssert\NotEmptyString()],
            'lastName' => [new Assert\NotBlank(), new OwnAssert\NotEmptyString()],
            'email' => [
                new Assert\NotBlank(),
                new Assert\Email(),
            ],
            'password' => [new Assert\NotBlank(), new OwnAssert\NotEmptyString()],
            'role' => new Assert\Choice([Role::ADMIN, Role::CHIPPER, Role::USER]),
        ]));
    }
}