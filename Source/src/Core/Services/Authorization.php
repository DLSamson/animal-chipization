<?php

namespace Api\Core\Services;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Models\Account;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class Authorization
{
    public const SUCCESS = 1;
    public const FAIL = 2;
    public const NULL = 3;

    protected static ?Account $currentAccount = null;

    public static function getCurrentAccount(): ?Account
    {
        return self::$currentAccount;
    }

    public static function Auth($authHash = '')
    {
        if ($authHash == '') return self::NULL;

        $authHash = str_replace('Basic ', '', $authHash);
        [$email, $password] = explode(':', base64_decode($authHash));

        $account = Account::where(['email' => $email])->first();
        if (!$account) return self::FAIL;
        self::$currentAccount = $account;

        $result = password_verify($password, $account->password);

        if (!$result)
            var_dump([$account->id, $account->email, $account->password]);

        return $result
            ? self::SUCCESS
            : self::FAIL;
    }

    public static function AuthAllowNull(Request $request, RequestHandler $requestHandler): ResponseInterface
    {
        $auth_code = self::Auth($request->getHeaderLine('Authorization'));
        if ($auth_code === self::FAIL)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_UNAUTHORIZED);

        return $requestHandler->handle($request);
    }

    public static function AuthStrict(Request $request, RequestHandler $requestHandler): ResponseInterface
    {
        $auth_code = self::Auth($request->getHeaderLine('Authorization'));
        if ($auth_code !== self::SUCCESS)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_UNAUTHORIZED);

        return $requestHandler->handle($request);
    }

    public static function AuthNotAllowed(Request $request, RequestHandler $requestHandler): ResponseInterface
    {
        $auth_code = self::Auth($request->getHeaderLine('Authorization'));
        if ($auth_code === self::SUCCESS)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_FORBIDDEN);

        return $requestHandler->handle($request);
    }
}