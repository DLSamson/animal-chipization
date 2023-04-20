<?php

use Api\Core\Factories\ResponseFactory;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;

if (!defined('ROOT_PATH'))
    define('ROOT_PATH', dirname(__DIR__));

/* For location precision */
ini_set('precision', 17);

/* Folders */
if (!is_dir(ROOT_PATH . '/log'))
    mkdir(ROOT_PATH . '/log');

if (!is_dir(ROOT_PATH . '/cache'))
    mkdir(ROOT_PATH . '/cache');

if (!is_dir(ROOT_PATH . '/cache/templates'))
    mkdir(ROOT_PATH . '/cache/templates');

if (!is_dir(ROOT_PATH . '/templates'))
    mkdir(ROOT_PATH . '/templates');

/* Container */
$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(true);
$containerBuilder->addDefinitions([
    LoggerInterface::class => DI\factory(function () {
        return new Logger('main', [new StreamHandler(ROOT_PATH . '/log/main.log', Logger::INFO)]);
    }),
    ValidatorInterface::class => DI\factory(function () {
        return (new ValidatorBuilder())->getValidator();
    }),
    Fenom::class => DI\factory(function () {
        return Fenom::factory(ROOT_PATH . '/templates', ROOT_PATH . '/cache/templates',
            Fenom::AUTO_RELOAD | Fenom::AUTO_STRIP | Fenom::AUTO_ESCAPE
        );
    }),
    'env' => function () {
        $dotenv = Dotenv::createImmutable(ROOT_PATH . '/.env');
        return $dotenv->safeLoad();
    },
]);

/* Env file */
$dotenv = Dotenv::createImmutable(ROOT_PATH);
$dotenv->safeLoad();

/* Container and Eloquent */
try {
    $container = $containerBuilder->build();
    $eloquent = new Capsule();
    $eloquent->addConnection([
        'driver' => $_ENV['DB_TYPE'],
        'host' => $_ENV['DB_HOST'],
        'database' => $_ENV['DB_NAME'],
        'username' => $_ENV['DB_USERNAME'],
        'password' => $_ENV['DB_PASSWORD'],
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => !empty($_ENV['DB_PREFIX']) ? $_ENV['DB_PREFIX'] : '',
    ]);
    $eloquent->setAsGlobal();
    $eloquent->bootEloquent();

} catch (Throwable $error) {
    echo $error->getTraceAsString();
}

/* Slim App */
AppFactory::setContainer($container);
$app = AppFactory::create();
$error_middleware = $app->addErrorMiddleware(true, true, true,
    $container->get(LoggerInterface::class));

$error_middleware->setErrorHandler(HttpNotFoundException::class, function () {
    return ResponseFactory::Make('<h1>404. Not found<h1><h2>Slim Web Api</h2>')
        ->Custom(404, 'Not found');
});