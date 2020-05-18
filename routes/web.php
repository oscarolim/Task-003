<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/',  function () use ($router) {
    return $router->app->version();
});

$router->group([
    'prefix' => 'api/v1'
], function () use ($router){
    $router->get('/spaceships', 'SpaceshipController@index');
    $router->get('/spaceship/{id}', 'SpaceshipController@show');

    $router->get('/armaments', 'ArmamentController@index');
    $router->get('/armament/{id}', 'ArmamentController@show');
});

$router->group([
    'prefix' => 'api/v1',
    'middleware' => 'auth'
], function () use ($router){
    $router->post('/spaceship', 'SpaceshipController@store');
    $router->put('/spaceship/{id}', 'SpaceshipController@update');
    $router->delete('/spaceship/{id}', 'SpaceshipController@destroy');
    $router->post('/spaceship/{id}/armament/install', 'SpaceshipController@installArmament');
    $router->delete('/spaceship/{id}/armament/remove', 'SpaceshipController@removeArmament');

    $router->post('/armament', 'ArmamentController@store');
    $router->put('/armament/{id}', 'ArmamentController@update');
    $router->delete('/armament/{id}', 'ArmamentController@destroy');
});
