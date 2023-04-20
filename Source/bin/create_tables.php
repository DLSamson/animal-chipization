<?php

error_reporting(E_ALL);
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/bootstrap.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

/* Drop existing tables */
Capsule::schema()->dropIfExists('animals_locations');
Capsule::schema()->dropIfExists('animals_types');
Capsule::schema()->dropIfExists('animals');
Capsule::schema()->dropIfExists('types');
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

    /* Since I have no idea how to make php work fine with high precision float values */
//    $table->float('latitude', 20, 16);
//    $table->float('longitude', 20, 16);
    $table->string('latitude');
    $table->string('longitude');

    $table->timestamps();
    $table->softDeletes();
});
Capsule::schema()->create('types', function (Blueprint $table) {
    $table->id();

    $table->string('type');

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

    $table->timestamp('chippingDateTime')->default(Capsule::raw('CURRENT_TIMESTAMP'));
    $table->timestamp('deathDateTime')->nullable()->default(null);

    $table->timestamps();
    $table->softDeletes();
});
Capsule::schema()->create('animals_types', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('animal_id');
    $table->unsignedBigInteger('type_id');

    $table->foreign('animal_id')->references('id')
        ->on('animals')->onDelete('cascade');
    $table->foreign('type_id')->references('id')
        ->on('types')->onDelete('cascade');
});
Capsule::schema()->create('animals_locations', function (Blueprint $table) {
    $table->id();

    $table->unsignedBigInteger('animal_id')->unsigned();
    $table->unsignedBigInteger('location_id')->unsigned();
    $table->timestamp('dateTimeOfVisitLocationPoint')
        ->default(Capsule::raw('CURRENT_TIMESTAMP'));

    $table->foreign('animal_id')->references('id')
        ->on('animals')->onDelete('cascade');
    $table->foreign('location_id')->references('id')
        ->on('locations')->onDelete('cascade');

    $table->timestamps();
    $table->softDeletes();
});


Capsule::schema()->dropIfExists('areas');
//Capsule::schema()->create('areas', function (Blueprint $table) {
//    $table->id();
//
//    $table->string('name');
//    $table->polygon('area');
//
//    $table->timestamps();
//    $table->softDeletes();
//});

/* Since we cannot use PostGis and I don't want to spend time for searching appropriate libs  */
Capsule::select('CREATE TABLE areas (
      id SERIAL PRIMARY KEY,
      name VARCHAR(255),
      "areaPoints" POLYGON,
      created_at TIMESTAMP,
      updated_at TIMESTAMP,
      deleted_at TIMESTAMP
    );
');

echo 'DATABASE CREATED' . PHP_EOL;