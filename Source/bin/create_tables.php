<?php

error_reporting(E_ALL);
require_once dirname(__DIR__).'/vendor/autoload.php';
require_once dirname(__DIR__).'/config/bootstrap.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

/* Drop existing tables */
Capsule::schema()->dropIfExists('accounts');
Capsule::schema()->dropIfExists('roles');

Capsule::schema()->create('roles', function (Blueprint $table) {
    $table->id();

    $table->string('name');

    $table->timestamps();
    $table->softDeletes();
});

/* Create your tables */
Capsule::schema()->create('accounts', function (Blueprint $table) {
    $table->id();

    $table->string('firstName');
    $table->string('lastName');
    $table->string('email');
    $table->string('password');

    /* Base value for USER role */
    /* WARNING! It actually highly depends on bin/fill_tables.php */
    $table->unsignedBigInteger('role_id')->default(3);
    $table->foreign('role_id')
        ->references('id')->on('roles');

    $table->timestamps();
    $table->softDeletes();
});

echo 'DATABASE CREATED'.PHP_EOL;