EpicRoute - A very fast router for PHP
=======================================

With this library you can easily implement routing in your PHP application, and it's insanely fast!

Installation
-----

To install with composer:

```sh
composer require kito92/epicroute
```

Requires PHP 5.3+.


Basic Usage
-----

A simple file setup is located under 'example' directory.
Here's a basic usage example:

```php
<?php
require 'vendor/autoload.php';
$route = \EpicRoute\Route::getInstance();

$route->get('/', function(){
    echo 'response';
});

$route->dispatch();
```

Available methods are the following:

```php
$route->get('/...', function(){ /*...*/ });
$route->post('/...', function(){ /*...*/ });
$route->put('/...', function(){ /*...*/ });
$route->patch('/...', function(){ /*...*/ });
$route->delete('/...', function(){ /*...*/ });
```

Variables & Regex
-----

Here's how you can match a variable:

```php
$route->get('/users/:name', function($name){
    echo 'Welcome back ' . $name;
});
```

and in this way you can match only numeric value:

```php
$route->get('/users/:id{[0-9]*}', function($id){
    echo 'user id ' . $id;
});
```

To match all the sub-URLs you can add the "+" at the end of a variable's name:

```php
$route->get('/:slug+', function($paths){
    print_r($paths);
});
```

This will match `/any/route/you/goes`, and the variable `$paths` is an array which contains
the subfolder requested.

Remember that, for the same base url, if you have multiple regex route and a generic one,
you HAVE to declare first those who have a regex, than the generic one, or the router
will serve always the generic.
You can call several methods through the fluent interface.
So, for example:

```php
$route->get('/users/:id{[0-9]*}', function($id){ /*...*/ })
	  ->get('/users/:name', function($name){ /*...*/ })
	  ->dispatch();
```

Middleware
-----

You can define your custom middleware implementing the \EpicRoute\Middleware interface:

```php
class CustomMiddleware implements \EpicRoute\Middleware{
    /**
     * This method will be executed before the routing
     *
     * @return mixed
     */
    function before(){
        // TODO: Implement before() method.
    }

    /**
     * This method will be executed after the routing
     *
     * @return mixed
     */
    function after(){
        // TODO: Implement after() method.
    }
}
```

and you can associate to a route in this way:

```php
$route->get('/', function(){
    echo 'home';
}, ['middleware' => CustomMiddleware::class]);
```

### Group

If you want to set a middleware to a group of routes, you can declare a group of view:

```php
$route->group(['middleware' => CustomMiddleware::class], function () use ($route){
    $route->get('/users/:id{[0-9]*}', function ($id){
        echo 'user id ' . $id;
    });
    $route->get('/users/:name', function ($name){
        echo 'Welcome back ' . $name;
    });
})->dispatch();
```

License
-----

The MIT License (MIT). Please see [License File](https://github.com/Kito92/EpicRoute/blob/master/LICENSE) for more information.
