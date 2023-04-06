<?php

error_reporting(E_ALL);
require_once dirname(__DIR__).'/vendor/autoload.php';
require_once dirname(__DIR__).'/config/bootstrap.php';

use Api\Core\Models\Role;
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

/* USER role must has id 3 */
/* WARNING! It actually highly depends on bin/create_tables.php */
foreach (['ADMIN', 'CHIPPER', 'USER'] as $roleName) {
    $role = new Role(['name' => $roleName]);
    $role->save();
    $roles[$roleName] = $role->id;
}

$data = json_decode($json, true);
foreach($data as $userData) {
    $userData['password'] = Account::HashPassword($userData['password']);
    $userData['role_id'] = $roles[$userData['role']];
    $account = new Account($userData);
    $account->save();
}

echo 'DATA FILLED TO DATABASE'.PHP_EOL;