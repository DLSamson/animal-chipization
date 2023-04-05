<?php

error_reporting(E_ALL);
require_once dirname(__DIR__).'/vendor/autoload.php';
require_once dirname(__DIR__).'/config/bootstrap.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

/* Create your tables */
Capsule::schema()->dropIfExists('users');
Capsule::schema()->create('users', function (Blueprint $table) {
    $table->id();

    $table->string('username');
    $table->string('email');
    $table->string('password');

    $table->timestamps();
    $table->softDeletes();
});

echo 'DATABASE CREATED';