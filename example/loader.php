<?php
require 'path/to/vendor/autoload.php';
require 'CustomMiddleware.php';

$route = \EpicRoute\Route::getInstance();

$route->get('/', function(){
    echo 'home';
});

$route->group(['middleware' => CustomMiddleware::class], function () use ($route){
    $route->get('/users/:id{[0-9]*}', function ($id){ echo 'user id ' . $id; })
          ->get('/users/:name', function ($name){ echo 'Welcome back ' . $name;});
});