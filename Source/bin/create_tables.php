<?php

error_reporting(E_ALL);
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/bootstrap.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

/* Drop existing tables */
Capsule::schema()->dropIfExists('animals');
Capsule::schema()->dropIfExists('locations');
Capsule::schema()->dropIfExists('accounts');

/* Create your tables */
Capsule::schema()->create('accounts', function (Blueprint $table) {
    $table->id();

    $table->string('firstName');
    $table->string('lastName');
    $table->string('email');
    $table->string('password');
    $table->string('role');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('locations', function (Blueprint $table) {
    $table->id();

    $table->float('latitude', 23, 20);
    $table->float('longitude', 23, 20);

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('animals', function (Blueprint $table) {
    $table->id();

    $table->float('weight', 25, 10);
    $table->float('length', 25, 10);
    $table->float('height', 25, 10);

    $table->string('gender');
    $table->string('lifeStatus')->default('ALIVE');

    $table->unsignedBigInteger('chipperId');
    $table->foreign('chipperId')
        ->references('id')->on('accounts');

    $table->unsignedBigInteger('chippingLocationId');
    $table->foreign('chippingLocationId')
        ->references('id')->on('locations');

    $table->timestamp('deathDateTime')->nullable()->default(null);

    $table->timestamps();
    $table->softDeletes();
});

echo 'DATABASE CREATED' . PHP_EOL;