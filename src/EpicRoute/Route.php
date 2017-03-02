<?php

namespace EpicRoute;

class Route{
    private static $instance = null;
    private $baseUrl = '';
    private $getRouteTree = [];
    private $postRouteTree = [];
    private $deleteRouteTree = [];
    private $putRouteTree = [];
    private $patchRouteTree = [];
    private $routeNames = [];
    private $addToNext = [];
    private $matchParams = [];

    private function __construct(){}

    /**
     * @return Route
     */
    static function getInstance(){
        if(static::$instance == null){
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Setting the base url for the routing
     *
     * @param string $baseUrl
     * @return $this
     */
    public function setBaseUrl($baseUrl){
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * Generate the tree containing the route and the relative view
     *
     * @param array $route_tree
     * @param array $elements
     * @param $view
     * @param Middleware $middleware|array
     */
    private function buildTree(array &$route_tree, array $elements, $view, $middleware = null) {
        $last = &$route_tree;
        foreach ($elements as $element) {
            $next = array();
            if(($startRegex = strpos($element, '{')) !== FALSE && ($endRegex = strpos($element, '}')) !== FALSE){
                $next['regex'] = substr($element, $startRegex+1, $endRegex-$startRegex-1);
                $element = substr($element, 0, $startRegex);
            }
            if(substr($element, -1) == '+'){
                $next['morechild'] = true;
                $element = substr($element, 0, -1);
            }
            if(!isset($last[$element])) {
                $last[$element] = $next;
            }
            $last = &$last[$element];
        }
        $route = ['view' => $view];
        if($middleware !== null){
            $route['middleware'] = $middleware;
        }
        $last[''] = $route;
    }

    private function splitUrl($url){
        if($url == '/'){
            return [''];
        }
        $arr = array();
        $tok = strtok($url, '/');

        while ($tok !== false) {
            $arr[] = $tok;
            $tok = strtok('/');
        }
        return $arr;
    }

    /**
     * Matching the GET $url with the class of function $view
     *
     * @param string $url
     * @param string $view
     * @param array $options array with the options
     * @return $this
     */
    public function get($url, $view, array $options = null){
        if($options != null) {
            $options = array_merge_recursive($options, $this->addToNext);
        } else {
            $options = $this->addToNext;
        }
        $middleware = null;
        if(isset($options['middleware'])){
            $middleware = $options['middleware'];
        }
        self::buildTree($this->getRouteTree, static::splitUrl($url), $view, $middleware);
        if(isset($options['name']) && $options['name'] != null)
            $this->routeNames[$options['name']] = $url;
        return $this;
    }

    /**
     * Matching the POST $url with the class of function $view
     *
     * @param string $url
     * @param string $view
     * @param array $options
     * @internal param null $string $name
     * @return $this
     */
    public function post($url, $view, array $options = null){
        if($options != null) {
            $options = array_merge($options, $this->addToNext);
        } else {
            $options = $this->addToNext;
        }
        $middleware = null;
        if(isset($options['middleware'])){
            $middleware = $options['middleware'];
        }
        $this->buildTree($this->postRouteTree, static::splitUrl($url), $view, $middleware);
        return $this;
    }

    /**
     * Matching the PUT $url with the class of function $view
     *
     * @param string $url
     * @param string $view
     * @param array $options
     * @internal param null $string $name
     * @return $this
     */
    public function put($url, $view, array $options = null){
        if($options != null) {
            $options = array_merge($options, $this->addToNext);
        } else {
            $options = $this->addToNext;
        }
        $middleware = null;
        if(isset($options['middleware'])){
            $middleware = $options['middleware'];
        }
        $this->buildTree($this->putRouteTree, static::splitUrl($url), $view, $middleware);
        return $this;
    }

    /**
     * Matching the PATCH $url with the class of function $view
     *
     * @param string $url
     * @param string $view
     * @param array $options
     * @internal param null $string $name
     * @return $this
     */
    public function patch($url, $view, array $options = null){
        if($options != null) {
            $options = array_merge($options, $this->addToNext);
        } else {
            $options = $this->addToNext;
        }
        $middleware = null;
        if(isset($options['middleware'])){
            $middleware = $options['middleware'];
        }
        $this->buildTree($this->patchRouteTree, static::splitUrl($url), $view, $middleware);
        return $this;
    }

    /**
     * Matching the DELETE $url with the class of function $view
     *
     * @param string $url
     * @param string $view
     * @param array $options
     * @internal param null $string $name
     * @return $this
     */
    public function delete($url, $view, array $options = null){
        if($options != null) {
            $options = array_merge($options, $this->addToNext);
        } else {
            $options = $this->addToNext;
        }
        $middleware = null;
        if(isset($options['middleware'])){
            $middleware = $options['middleware'];
        }
        $this->buildTree($this->deleteRouteTree, static::splitUrl($url), $view, $middleware);
        return $this;
    }

    /**
     * @param array $options
     * @param callable $callback
     * @return $this
     */
    public function group(array $options, callable $callback){
        $prev = $this->addToNext;
        $this->addToNext = array_merge_recursive($prev, $options);
        $callback();
        $this->addToNext = $prev;
        return $this;
    }

    /**
     * Get the current HTTP request method.
     *
     * @return string
     */
    private function getMethod(){
        if (isset($_POST['_method'])) {
            return strtoupper($_POST['_method']);
        } else {
            return $_SERVER['REQUEST_METHOD'];
        }
    }

    /**
     * Return the view matching the given $url
     *
     * @param string $url
     * @return mixed|null
     */
    private function findView($url){
        $this->matchParams = [];
        $path = explode('/', $url);
        if(sizeof($path) > 1){
            $path = array_filter($path);
        }
        switch (static::getMethod()){
            case 'GET':
                $next = &$this->getRouteTree;
                break;
            case 'POST':
                $next = &$this->postRouteTree;
                break;
            case 'DELETE':
                $next = &$this->deleteRouteTree;
                break;
            case 'PUT':
                $next = &$this->putRouteTree;
                break;
            case 'PATCH':
                $next = &$this->patchRouteTree;
                break;
        }
        for($i = 0; $i < sizeof($path); $i++){
            $sub = $path[$i];
            if(isset($next[$sub])){
                $next = &$next[$sub];
            } else {
                $found = null;
                $keys = array_keys($next);
                foreach ($keys as $key){
                    if(strpos($key, ':') === 0){
                        if(isset($next[$key]['regex']) && !preg_match('/^' . $next[$key]['regex'] . '$/', $sub)){
                            continue;
                        }
                        if(isset($next[$key]['morechild'])) {
                            $arr = array();
                            for($j = $i; $j < sizeof($path); $j++){
                                $arr[] = $path[$j];
                            }
                            $this->matchParams[] = preg_replace("/[^A-Za-z0-9-_]/", "", $sub);
                            return $next[$key][''];
                        }
                        $this->matchParams[] = preg_replace("/[^A-Za-z0-9-_]/", "", $sub);
                        $found = $key;
                        $next = &$next[$found];
                        break;
                    }
                }
                if($found != null){
                    //$next = &$next[$found];
                } else {
                    return null;
                }
            }
        }
        if(isset($next[''])) {
            return $next[''];
        }
        header("HTTP/1.0 404 Not Found");
        die();
    }

    /**
     * Showing the class defined in the method @addRoute.
     * @param string|null $path
     */
    public function dispatch($path = null){
        if($path == null){
            $path = $_SERVER['REQUEST_URI'];
        }
        $path_only = parse_url($path, PHP_URL_PATH);
        $n = strlen($this->baseUrl) + 1;
        $page = substr($path_only, $n);
        $result = self::findView(htmlspecialchars($page));

        if(!isset($result['view']) || $result['view'] == null){
            header("HTTP/1.0 404 Not Found");
            die();
        }

        if(isset($result['middleware'])){
            /* @var $middleware Middleware */
            if(is_subclass_of($result['middleware'], Middleware::class)){
                $middleware = new $result['middleware'];
                $middleware->before();
                static::executeAction($result['view']);
                $middleware->after();
            } else if(is_array($result['middleware'])){
                $reverseMW = array();
                foreach ($result['middleware'] as $mwName){
                    $middleware = new $mwName;
                    $middleware->before();
                    array_unshift($reverseMW, $middleware);
                }
                static::executeAction($result['view']);
                foreach ($reverseMW as $middleware){
                    $middleware->after();
                }
            }
        } else {
            static::executeAction($result['view']);
        }
    }

    private function executeAction($result){
        if(is_callable($result)){
            call_user_func_array($result, $this->matchParams);
        } else if(class_exists($result)){
            new $result();
        }
    }

    /**
     * Return the URL of a view named in the @addRoute method
     *
     * @param $name string of the view
     * @return string url of the view
     */
    public function getViewUrl($name){
        if(key_exists($name, $this->routeNames)){
            $url = $this->routeNames[$name];
        } else {
            $url = './';
        }
        return $url;
    }

    /**
     * Redirect to a view named in the @addRoute method
     *
     * @param $name string of the view
     * @param $getVar string getParams like var=value&var2=value2...
     */
    public function redirect($name, $getVar = null){
        $url = $this->baseUrl . '/' . static::getViewUrl($name);
        if($getVar != null){
            $url = $url . '?' . $getVar;
        }
        if($_SERVER['REQUEST_URI'] == $url && !headers_sent()){
            $_POST = array();
            static::dispatch($url);
        } else {
            header('Location: ' . $url);
            die();
        }
    }

    public function redirectBack(){
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        die();
    }
}