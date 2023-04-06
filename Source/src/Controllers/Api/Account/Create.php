<?php

namespace Api\Controllers\Api\Account;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Account;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;
use Api\Core\Validation\Constraints as OwnAssert;

class Create extends BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response {
        $json = $request->getBody()->getContents();
        $data = json_decode($json, true);

        if (Account::where(['email' => $data['email']])->first())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_CONFLICT);

        $user = new Account([
            'firstName'    => $data['firstName'],
            'lastName'     => $data['lastName'],
            'email'        => $data['email'],
            'passwordHash' => Account::HashPassword($data['password']),
        ]);

        if ($user->save()) return ResponseFactory::MakeJSON([
            'id'        => $user->id,
            'firstName' => $user->firstName,
            'lastName'  => $user->lastName,
            'email'     => $user->email,
        ])->Success();

        return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    protected function validateRequest(Request $request) : array {
        $json = $request->getBody()->getContents();
        $data = json_decode($json, true);

        return $this->validate($data, new Assert\Collection([
            'firstName' => [new Assert\NotBlank(), new OwnAssert\NotEmptyString()],
            'lastName'  => [new Assert\NotBlank(), new OwnAssert\NotEmptyString()],
            'email'     => [
                new Assert\NotBlank(),
                new Assert\Email(),
            ],
            'password'  => [new Assert\NotBlank(), new OwnAssert\NotEmptyString()],
        ]));
    }
}