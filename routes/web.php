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

$router->get('/products', 'ProductController@index'); // Fetch all products
$router->get('/products/{id}', 'ProductController@show'); // Fetch a product by ID
$router->post('/products', 'ProductController@store'); // Create a new product
$router->put('/products/{id}', 'ProductController@update'); // Update a product by ID
$router->delete('/products/{id}', 'ProductController@destroy'); // Delete a product by ID

$router->get('/', function () use ($router) {
    return $router->app->version();
});
