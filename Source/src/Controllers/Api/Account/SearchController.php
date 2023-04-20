<?php

namespace Api\Controllers\Api\Account;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Account;
use Api\Core\Services\Authorization;
use Api\Core\Services\Formatters\AccountFormatter;
use Fig\Http\Message\StatusCodeInterface;
use Illuminate\Database\Eloquent\Collection;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;

class SearchController extends BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $currentAccount = Authorization::getCurrentAccount();
        if ($currentAccount && !$currentAccount->isAdmin())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_FORBIDDEN);

        $params = $request->getQueryParams();
        $params['from'] = $params['from'] ?: 0;
        $params['size'] = $params['size'] ?: 10;

        $queryConditions = array_filter($params, fn($key) => !in_array($key, ['from', 'size']), ARRAY_FILTER_USE_KEY);
        $queryConditions = array_map(
            fn($key, $val) => [$key, 'ilike', "%$val%"], array_keys($queryConditions), $queryConditions);

        /* @var Collection $accounts */
        $accounts = Account::where($queryConditions)
            ->orderBy('id')
            ->offset($params['from'])
            ->limit($params['size'])
            ->get();

        return ResponseFactory::MakeJSON(AccountFormatter::PrepareMany($accounts))->Success();
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $params = $request->getQueryParams();
        $params['from'] = $params['from'] ?: 0;
        $params['size'] = $params['size'] !== null ? $params['size'] : 10;

        return $this->validate($params, new Assert\Collection([
            'firstName' => new Assert\Optional([new Assert\NotBlank()]),
            'lastName' => new Assert\Optional([new Assert\NotBlank()]),
            'email' => new Assert\Optional([new Assert\NotBlank()]),
            'from' => new Assert\Required([new Assert\NotBlank(), new Assert\PositiveOrZero()]),
            'size' => new Assert\Required([new Assert\NotBlank(), new Assert\Positive()]),
        ]));
    }
}