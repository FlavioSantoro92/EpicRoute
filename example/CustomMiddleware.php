<?php

class CustomMiddleware implements \EpicRoute\Middleware{
    /**
     * This method will be executed before the routing
     */
    function before(){
        echo 'Executed before the route.<br/>' . PHP_EOL;
    }

    /**
     * This method will be executed after the routing
     */
    function after(){
        echo PHP_EOL . '<br/>Executed after the route.';
    }
}