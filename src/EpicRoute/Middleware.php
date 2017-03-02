<?php

namespace EpicRoute;

interface Middleware {
    /**
     * This method will be executed before the routing
     */
    function before();

    /**
     * This method will be executed after the routing
     */
    function after();
}