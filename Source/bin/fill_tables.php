<?php

error_reporting(E_ALL);
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/bootstrap.php';

use Api\Core\Models\Account;

$json = '[{
        "id": 1,					
        "firstName": "adminFirstName",	
        "lastName": "adminLastName",	
        "email": "admin@simbirsoft.com",	
        "password": "qwerty123",		
        "role": "ADMIN"			
    },
    {
        "id": 2,					
        "firstName": "chipperFirstName",	
        "lastName": "chipperLastName",	
        "email": "chipper@simbirsoft.com",	
        "password": "qwerty123",		
        "role": "CHIPPER"			
    },
    {
        "id": 3,					
        "firstName": "userFirstName",		
        "lastName": "userLastName",		
        "email": "user@simbirsoft.com",	
        "password": "qwerty123",		
        "role": "USER"				
}]';

$data = json_decode($json, true);
foreach ($data as $userData) {
    $userData['password'] = Account::HashPassword($userData['password']);
    $account = new Account($userData);
    $account->save();
}

echo 'DATA FILLED TO DATABASE' . PHP_EOL;