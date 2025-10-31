# Animal Chipization

Этот репозиторий это результат моей работы в международной олимпиаде «IT-Планета 2023» в конкурсе компании SimbirSoft "Прикладное программирование if...else".

## What's included?

Here is a simple Slim Web API template

I used here these packages:
* [Slim](https://www.slimframework.com)
* [Monolog](https://seldaek.github.io/monolog/)
* [PHP-DI](https://php-di.org/)
* [Symfony Validator](https://symfony.com/doc/current/validation.html)
* [Eloquent](https://laravel.com/docs/10.x/eloquent)
* [Fenom](https://github.com/fenom-template/fenom)

It also has some support packages such as: 
* larapack/dd
* vlucas/phpdotenv

Other technologies:
* PHP-FPM 8.2
* Nginx
* PostgreSQL
* Adminer


## Start Working

You can start docker container with these commands

Firstly install all composer dependecies
```shell
cd Source
composer install
```

Then run docker-compose

```shell
cd ../Build 
docker-compose up
``` 

It will create database on first launch

## Config

Here is only 2 confg files. Bootstrap and routes. 
You may find them in `Source/config/`

Bootstrap is used to define DI Container and Eloquent connection, 
prepare Slim App 


## Database

All connection params can be passed as `environment` option in `Docker/docker-compose.yml` file.
Or you can create `Source/.env` file, you can look up for examples in 
`Source/.env.example`.

Define tables in database in `Source/bin/create_tables.php`
To create tables in database you can use command from Makefile

```shell
make database
```

## Routes

Define routes at `Source/config/routes.php`

You can find all information about defining routes at 
[Slim Routes Documentaion](https://www.slimframework.com/docs/v4/objects/routing.html)


## Controllers

All your business logic is located in `Source/src/`

Including your controllers in `Source/src/Controllers/`

For pages `Source/src/Controllers/Pages`

And API `Source/src/Controllers/Api`

You has 2 base controllers to extend `BaseController` 
and `RenderController`

`RenderController` has own render method.

You have two ways to use controllers;
* One controller for request
* Many requests for one controller class;

Basic usage is to override `proccess` method.

To validate request override `validate` method, 
it must return `null` if no errors, or array of errors.
For validation you may use `Symfony/Validator` and return it's violations.
They will be encoded in json and return `Response`.

When defining a route use `handle` method.
It will run `validate` method, then your `process` method 
and return the response.  

## Templates

As a template engine here is used 
[Fenom](https://github.com/fenom-template/fenom)
It has a smarty-like syntax

It's included in `RenderController`


## Makefile

Makefile has two commands
* database
* docker_init

database runs `Source/bin/create_tables.php` script

docker_init runs `Source/bin/docker_init.sh`. 
It will also try to create database

> That's literally means it will try to create database
> everytime you start the app with `docker-compose up`
> If you want to avoid it, just remove the first line from 
> `Source/bin/docker_init.sh`

## Remarks

In nginx service mounts only `Source/public` folder.
All php files are mounted only to php service container.

> Everythins seems to be working, but if you 
> want to mount the whole project to nginx service container, 
> just change it in `Docker/docker-compose.yml`
