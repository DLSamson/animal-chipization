<?php

/* @var \Slim\App $app */

use Api\Controllers\Api\Account;

use Api\Controllers\Api\EchoController;
use Api\Controllers\Pages\IndexController;
use Api\Core\Services\Authorization;
use Slim\Routing\RouteCollectorProxy;

/* Pages */
$app->get('/', [IndexController::class, 'handle'])->setName('pages.index');
$app->get('/info', function($req, $res) { phpinfo(); return $res->withStatus(200);});

$app->get('/test', function($req, $res) {

    return $res->withStatus(200);
});

/* Api */
$app->get('/echo/{value}', [EchoController::class, 'handle']);


/* Animal Chipization API */

$app->group('', function(RouteCollectorProxy $group) {

});

/* Not allowed for authorized users */
$app->group('', function (RouteCollectorProxy $group) {
    $group->post('/registration', [Account\Create::class, 'handle'])->setName('account.create');
})
    ->add([Authorization::class, 'AuthAllowNull'])
    ->add([Authorization::class, 'AuthNotAllowed']);