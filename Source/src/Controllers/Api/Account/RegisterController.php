<?php

namespace Api\Controllers\Api\Account;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Account;
use Api\Core\Models\Role;
use Api\Core\Services\Formatters\AccountFormatter;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;
use Api\Core\Validation\Constraints as OwnAssert;

class RegisterController extends BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $json = $request->getBody()->getContents();
        $data = json_decode($json, true);

        if (Account::where(['email' => $data['email']])->first())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_CONFLICT);

        $account = new Account([
            'firstName' => $data['firstName'],
            'lastName' => $data['lastName'],
            'email' => $data['email'],
            'password' => Account::HashPassword($data['password']),
            'role' => Role::DEFAULT,
        ]);

        if ($account->save())
            return ResponseFactory::MakeJSON(
                AccountFormatter::PrepareOne($account))
                ->Custom(StatusCodeInterface::STATUS_CREATED);

        return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $json = $request->getBody()->getContents();
        $data = json_decode($json, true);

        return $this->validate($data, new Assert\Collection([
            'firstName' => [new Assert\NotBlank(), new OwnAssert\NotEmptyString()],
            'lastName' => [new Assert\NotBlank(), new OwnAssert\NotEmptyString()],
            'email' => [
                new Assert\NotBlank(),
                new Assert\Email(),
            ],
            'password' => [new Assert\NotBlank(), new OwnAssert\NotEmptyString()],
        ]));
    }
}