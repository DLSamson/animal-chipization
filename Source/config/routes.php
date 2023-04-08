<?php

/* @var \Slim\App $app */

use Api\Controllers\Api\Account;
use Api\Controllers\Api\Location;

use Api\Controllers\Api\EchoController;
use Api\Controllers\Pages\IndexController;
use Api\Core\Factories\ResponseFactory;
use Api\Core\Services\Authorization;
use Api\Core\Services\Formatters\AccountFormatter;
use Fig\Http\Message\StatusCodeInterface;
use Illuminate\Database\Eloquent\Collection;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteCollectorProxy;

/* Pages */
$app->get('/', [IndexController::class, 'handle'])->setName('pages.index');
$app->get('/info', function ($req, $res) {
    phpinfo();
    return $res->withStatus(200);
});

$app->get('/test', function ($req, $res) {

    return $res->withStatus(200);
});

/* Api */
$app->get('/echo/{value}', [EchoController::class, 'handle']);


/* Animal Chipization API */

/* Requires authorization */
$app->group('', function (RouteCollectorProxy $group) {
    $group->group('/locations', function (RouteCollectorProxy $group) {
        $group->get('[/{pointId}]', [Location\GetController::class, 'handle'])->setName('locations.get');
        $group->post('', [Location\CreateController::class, 'handle'])->setName('locations.create');
        $group->put('[/{pointId}]', [Location\UpdateController::class, 'handle'])->setName('locations.update');
        $group->delete('[/{pointId}]', [Location\DeleteController::class, 'handle'])->setName('locations.delete');
    });

    $group->group('/accounts', function (RouteCollectorProxy $group) {
        $group->get('/search', [Account\SearchController::class, 'handle'])->setName('accounts.search');
        $group->get('[/{accountId}]', [Account\GetController::class, 'handle'])->setName('accounts.get');
        $group->post('', [Account\CreateController::class, 'handle'])->setName('account.create');
        $group->put('[/{accountId}]', [Account\UpdateController::class, 'handle'])->setName('account.update');
        $group->delete('[/{accountId}]', [Account\DeleteController::class, 'handle'])->setName('account.delete');
    });
})
    ->add([Authorization::class, 'AuthStrict']);

/* Not allowed for authorized users */
$app->group('', function (RouteCollectorProxy $group) {
    $group->post('/registration', [Account\RegisterController::class, 'handle'])->setName('account.create');
})
    ->add([Authorization::class, 'AuthAllowNull'])
    ->add([Authorization::class, 'AuthNotAllowed']);