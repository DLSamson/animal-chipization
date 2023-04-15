<?php

/* @var \Slim\App $app */

use Api\Controllers\Api\Account;
use Api\Controllers\Api\Location;
use Api\Controllers\Api\Type;
use Api\Controllers\Api\Animal;
use Api\Controllers\Api\AnimalType;
use Api\Controllers\Api\Area;

use Api\Controllers\Api\EchoController;
use Api\Controllers\Pages\IndexController;
use Api\Core\Factories\ResponseFactory;
use Api\Core\Services\Authorization;
use Fig\Http\Message\StatusCodeInterface;
use Slim\Routing\RouteCollectorProxy;

use Brick\Geo\Polygon;
use Brick\Geo\Point;
use Brick\Geo\LineString;
use Api\Core\Models;
use Symfony\Component\Validator\ValidatorBuilder;
use Symfony\Component\Validator\Constraints as Assert;
use Illuminate\Database\Capsule\Manager as Capsule;

/* Pages */
$app->get('/', [IndexController::class, 'handle'])->setName('pages.index');
$app->get('/info', function ($req, $res) {
    phpinfo();
    return $res->withStatus(200);
});
$app->get('/test', function ($req, $res) {
    $data = json_decode('{
    "name": "f08575dd-e580-40a3-89ae-8f975758b51d",
    "areaPoints": [
        {
            "latitude": 61.0,
            "longitude": -179.0
        },
        {
            "latitude": 61.0,
            "longitude": -166.0
        },
        {
            "latitude": 74.0,
            "longitude": -166.0
        }
    ]
} ', true);
    $pointsCount = count($data['areaPoints']);
    $data['areaPoints'] = \Api\Core\Models\Area::convertManyPointsToString($data['areaPoints']);

//    dump($data, $pointsCount);

    if ($pointsCount != 3) {
        if ($secondArea = \Api\Core\Models\Area::whereRawIntersects($data['areaPoints'])->first())
            return ResponseFactory::MakeJSON($secondArea->toArray())->Custom(StatusCodeInterface::STATUS_BAD_REQUEST,
                'The zone intersects with another zone');
    } else
        if ($area = Models\Area::hasIntersectingTriangles($data['areaPoints']))
            return ResponseFactory::MakeJSON($area->toArray())->Custom(StatusCodeInterface::STATUS_BAD_REQUEST,
                'The zone intersects with another zone');

    //Пересекает
    '((0,0), (0,2), (2,2), (2,0))';
    '((1,1), (1,3), (3,3), (3,1))';

    //Касается
    '((0,0), (0,2), (2,2), (2,0))';
    '((2,0), (2,2), (2,4), (4,0))';

    /* Два треугольника впритык */
    '(-166, 14), (-179, 14), (-172.5, 3), (-179, 1), (-166, 1), (-166, 7), (-180, 7)';

    return $res->withStatus(200);
});

/* Api */
$app->get('/echo/{value}', [EchoController::class, 'handle']);


/* Animal Chipization API */

/* Requires authorization */
$app->group('', function (RouteCollectorProxy $group) {
    $group->group('/areas', function (RouteCollectorProxy $group) {
        $group->get('[/{areaId}]', [Area\GetController::class, 'handle'])->setName('area.create');
        $group->post('', [Area\CreateController::class, 'handle'])->setName('area.create');
        $group->put('[/{areaId}]', [Area\UpdateController::class, 'handle'])->setName('area.update');
        $group->delete('[/{areaId}]', [Area\DeleteController::class, 'handle'])->setName('area.delete');
    });

    $group->group('/animals/types', function (RouteCollectorProxy $group) {
        $group->get('[/{typeId}]', [Type\GetController::class, 'handle'])->setName('type.get');
        $group->post('', [Type\CreateController::class, 'handle'])->setName('type.create');
        $group->put('[/{typeId}]', [Type\UpdateController::class, 'handle'])->setName('type.update');
        $group->delete('[/{typeId}]', [Type\DeleteController::class, 'handle'])->setName('type.delete');
    });

    $group->group('/animals', function (RouteCollectorProxy $group) {
        $group->get('/search', [Animal\SearchController::class, 'handle'])->setName('animal.search');
        $group->get('[/{animalId}]', [Animal\GetController::class, 'handle'])->setName('animal.get');
        $group->post('', [Animal\CreateController::class, 'handle'])->setName('animal.create');
        $group->put('[/{animalId}]', [Animal\UpdateController::class, 'handle'])->setName('animal.update');
        $group->delete('[/{animalId}]', [Animal\DeleteController::class, 'handle'])->setName('animal.delete');

        $group->group('/{animalId}/types', function (RouteCollectorProxy $group) {
            $group->post('[/{typeId}]', [AnimalType\AddController::class, 'handle'])->setName('animal.type.add');
            $group->put('[/{typeId}]', [AnimalType\UpdateController::class, 'handle'])->setName('animal.type.update');
            $group->delete('[/{typeId}]', [AnimalType\DeleteController::class, 'handle'])->setName('animal.type.delete');
        });
    });

    $group->group('/locations', function (RouteCollectorProxy $group) {
        $group->get('[/{pointId}]', [Location\GetController::class, 'handle'])->setName('location.get');
        $group->post('', [Location\CreateController::class, 'handle'])->setName('location.create');
        $group->put('[/{pointId}]', [Location\UpdateController::class, 'handle'])->setName('location.update');
        $group->delete('[/{pointId}]', [Location\DeleteController::class, 'handle'])->setName('location.delete');
    });

    $group->group('/accounts', function (RouteCollectorProxy $group) {
        $group->get('/search', [Account\SearchController::class, 'handle'])->setName('account.search');
        $group->get('[/{accountId}]', [Account\GetController::class, 'handle'])->setName('account.get');
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