<?php

$router->get('/api-docs', 'Swagger\SwaggerController@docs');

$router->group(['prefix' => 'user'], function () use ($router) {
    $router->post('/spreadsheet', 'User\UserController@importSpreadsheet');
    $router->get('/spreadsheet', 'User\UserController@exportSpreadsheet');
    $router->get('/', 'User\UserController@findAllUsers');
    $router->get('/{id}', 'User\UserController@showById');
    $router->get('/{id}/eligibility', 'User\UserController@eligibility');
    $router->put('/{id}', 'User\UserController@update');
    $router->delete('/{id}', 'User\UserController@delete');
});
