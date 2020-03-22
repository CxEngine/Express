<?php

/**
 * Regex to search for variable names when calling the ->use function in a Router
 *
 * @var string
 */
const REGEX_VAR = '/:([a-zA-Z_\-0-9]+)\//i';

/**
 * Regex to find for a variable sent using the `use` function in a Router.
 * This will be used when getting the var content in an URL
 *
 * @var string
 */
const REGEX_VAR_URL = '([a-zA-Z0-9_\-@]+)/';


class Express
{
    /**
     * The url we are handling (the value in the ?route querystring sent by htaccess)
     * @var string
     */
    public $current;

    /**
     * The method used in the current request
     * @var string
     */
    public $method;

    /**
     * The headers in the current request
     * @var array
     */
    public $headers;

    /**
     * The parsed body in the current request
     * @var object
     */
    public $body;

    /**
     * The cookies in this request
     * @var object
     */
    public $cookies;

    /**
     * The querystring of this request
     * @var array
     */
    public $query;

    /**
     * Variables avaible within the entire instance and avaible in the template views
     * @var stdClass
     */
    public $locals;

    /**
     * A list of middlewares (aka pending Router instances)
     * @var array
     */
    protected $middlewares = [];

    /**
     * The default settings for ExpressPHP
     * @var array
     */
    protected $settings = array(
        /**
         * Default template engine
         * @var string
         */
        'view_engine'    => '',

        /**
         * The path where the templates are
         * @var string
         */
        'views'            => '',

        /**
         * Allow the execution of PHP within the templates?
         * @var boolean
         */
        'allow_php'        => true,

        /**
         * Make the output of json pretty
         * @var boolean
         */
        'pretty_json'    => false,

        /**
         * Make the templates render pretty
         * @var boolean
         */
        'pretty_print'    => false,

        /**
         * Where the cache of templates should be
         * @var string
         */
        'cache_dir'        => '/tmp'
    );

    public $basePath;

    /**
     * Gets the info of the current request
     */
    public function __construct()
    {

        // switch ($this->method) {
        //     case 'POST':
        //         // TODO: Better check for POST data
        //         if (count($_POST) > 0) {
        //             // Classic POST
        //             $this->body = (object) $_POST;
        //         } else {
        //             // JSON POST
        //             $this->body = json_decode(file_get_contents('php://input'));
        //         }
        //         break;
        //     case 'PUT':
        //         try {
        //             $this->body = json_decode(file_get_contents('php://input'));
        //         } catch (Exception $e) {
        //             throw new \Exception("Failed to parse PUT body");
        //             $this->body = (object) [];
        //         }
        //         break;
        //     default:
        //         $this->body = (object) [];
        // }
    }

    /**
     * Plug-in a middleware (Router)
     *
     * @param Router $router An \Express\Router instance
     * @return void
     */
    public function use($middleware)
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * Gets the collected info
     *
     * @param bool Return the results instead of dump
     * @return mixed
     */
    public function getInfo($return = false)
    {
        $info = array(
            'QueryString'        => $_SERVER['QUERY_STRING'],
            'ParsedQueryString'    => $this->query,
            'ParsedURL'            => $this->current,
            'Headers'            => $this->headers,
            'Cookies'            => $this->cookies,
            'Body'                => $this->body,
            'PHPVersion'        => phpversion()
        );

        if ($return) {
            return $info;
        }
    }

    /**
     * An alias for $this->setting()
     */
    public function set($setting, $value)
    {
        return $this->get($setting, $value);
    }

    /**
     * Gets or sets a setting
     *
     * @param string The variable name
     * @param string The value of the setting
     * @return string
     */
    public function get($setting, $value = '')
    {
        // View Engine -> view_engine
        // $setting = strtolower(str_replace(' ', '_', $setting));

        if ($value != '') {
            $this->settings[$setting] = $value;
        }

        return (isset($this->settings[$setting])) ? $this->settings[$setting] : false;
    }

    /**
     * A helper to make the updates we need to a path
     *
     * @param string The path to parse
     * @return string Valid path
     */
    private function parse($path = '')
    {
        // Add trailing /
        if (substr($path, -1) != '/') {
            $path = "$path/";
        }

        // Add first /
        if (substr($path, 0, 1) != '/') {
            $path = "/$path";
        }

        return $path;
    }

    /**
     * handle the request.
     *
     * @param Router An instance of Router (@see \Express\Router)
     * @return void
     */
    public function match($router, $requestUri = null)
    {
        $requestUri = $requestUri ? $requestUri : $this->current;

        // Prepare middlewares routes
        foreach ($this->middlewares as $middleware) {
            $router->use('', $middleware);
        }

        // Get routes from main router
        $routes = $router->getRoutes();

        // if (!isset($routes[$this->method])) {
        //     throw new \Exception('Could not handle ' . $this->method);
        // }

        // Merge * with current VERB
        // $arr = array_merge($routes['*'], $routes[$this->method]);
        $arr = array_filter($routes, function ($var) {
            return $var['method'] === '*' || $var['method'] === $this->method;
        });
        // Debug
        // print_r($arr);

        // Get all matches
        $matches = [];
        foreach ($arr as $key => $route) {
            $path = $route['route'];
            $path = $this->parse($path);

            // Build the regex to match the request
            $regex = preg_replace(REGEX_VAR, REGEX_VAR_URL, $path);
            $regex = str_replace('/', '\/', $regex);

            if (!$route['route']) {   // Middleware without route/path
                $matches[] = $route;
            } else if (preg_match('/^' . $regex . '$/', $requestUri)) {
                $matches[] = $route;
            } else {
                // No Match
            }
        }

        return $matches;
    }

    /**
     * handle the request.
     *
     * @param Router An instance of Router (@see \Express\Router)
     * @return void
     */
    public function listen($router)
    {

        $getRequestUrl = function ($basePath) {
            // Get requestUrl from $_SERVER['REQUEST_URI']
            $requestUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

            // strip base path from request url
            $requestUrl = substr($requestUrl, strlen($basePath));

            // Strip query string (?a=b) from Request Url
            if (($strpos = strpos($requestUrl, '?')) !== false) {
                $requestUrl = substr($requestUrl, 0, $strpos);
            }
            return $requestUrl;
        };

        $getHeaders = function () {
            $headers = array();
            foreach ($_SERVER as $k => $v) {
                if (substr($k, 0, 5) == "HTTP_") {
                    $k = str_replace('_', ' ', substr($k, 5));
                    $k = str_replace(' ', '-', ucwords(strtolower($k)));
                    $headers[$k] = $v;
                }
            }
            return $headers;
        };

        $requestUri = $getRequestUrl($this->get('basePath'));
        // Without trailing /
        $this->requestUri = $requestUri;
        // With trailing /
        $this->current = $this->parse((isset($requestUri)) ? $requestUri : '/');
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->headers = $getHeaders();
        $this->cookies = (object) $_COOKIE;
        $this->locals = new \stdClass;

        // Get the querystring, remove the route from it
        $querystring = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : "";
        parse_str($querystring, $result);
        unset($result['route']);
        $this->query = (object) $result;

        // Obtain the request body
        if (count($_POST) > 0) {
            // Classic POST
            $this->body = (object) $_POST;
        } else {
            // JSON POST
            $this->body = json_decode(file_get_contents('php://input'));
        }

        $matches = $this->match($router);

        /**
         * Parse variables from requestUri and path like /:api/:name
         * 
         * $parseVariables("/test/:id", "/test/123") => ['id'=>123]
         *
         * @param Router An instance of Router (@see \Express\Router)
         * @return void
         */
        $parseVariables = function ($path = '', $requestUri = '') {
            $path = "$path/";

            // Variables to be sent to the handler in the $response var
            $variables = array();

            // Get a list of the expected vars to send them in the $res variable
            preg_match_all(REGEX_VAR, $path, $var_result, PREG_PATTERN_ORDER);

            foreach ($var_result[1] as $var) {
                $variables[$var] = '';
            }

            // Build the regex to match the request
            $regex = preg_replace(REGEX_VAR, REGEX_VAR_URL, $path);
            $regex = str_replace('/', '\/', $regex);

            if (preg_match('/^' . $regex . '$/', $requestUri)) {
                // Get a list of the expected vars content
                preg_match_all('/^' . $regex . '$/', $requestUri, $body_result, PREG_SET_ORDER);

                $i = 1;
                foreach ($variables as $name => $content) {
                    if (!empty($body_result[0][$i])) {
                        $variables[$name] = $body_result[0][$i];
                        $i++;
                    }
                }
            }
            return $variables;
        };

        // Build the response parameter
        $response = new Response($this->settings);

        $i = 0;
        // Build the $next parameter
        $next = function () use ($matches, $parseVariables, $response, &$i, &$next) {
            // No more next ?
            if (!isset($matches[$i])) {
                return;
            }

            // Get route object
            $route = $matches[$i];

            // Convert request to params
            $variables = $parseVariables($route['route'], $this->current);

            // Call handler
            // Build the $request parameter
            $request = (object) array(
                'settings'    => $this->settings,
                'path'        => $this->current,
                'params'    => (object) $variables,
                'headers'    => $this->headers,
                'query'        => $this->query,
                'cookies'    => $this->cookies,
                'body'        => $this->body
            );

            $handler = $matches[$i]['callback'];

            // Increase i
            $i++;

            // if($i > 30) exit();
            if (!is_callable($handler)) return;

            // Call next match
            $handler($request, $response, $next);
            exit();
        };

        // Start chain
        $next();
    }
}


/**
 * This class is called automatically when handling a request and shouldn't be called directly
 */

class Response
{
    /**
     * An array of headers to be sent
     * @var array
     */
    private $headers;

    /**
     * The settings of the instance
     * @var array
     */
    private $settings;

    /**
     * Have we sent cookies in this response?
     * @var boolean
     */
    private $cookies = false;

    /**
     * Variables avaible within the entire instance (@see \Express\Express)
     * @var stdClass
     */
    private $locals;

    /**
     * An instance of the view engine
     */
    private $engine;

    /**
     * Constructor
     *
     * @param array The default express settings
     * @param stdClass An object with the app locals (@see \Express\Express)
     * @return void
     */
    public function __construct($settings = [], $locals = null)
    {
        $this->headers = [];
        $this->settings = $settings;

        if (!$locals) {
            $this->locals = new \stdClass;
        }

        $this->engine = $settings['view_engine'];

        // if (in_array($this->settings['view_engine'], array('jade', 'pug'))) {
        //     $this->engine = new Jade($this->settings['cache_dir'], $this->settings['pretty_print']);
        // } elseif ($this->settings['view_engine'] == 'mustache') {
        //     $this->engine = new \Mustache_Engine();
        // }
    }

    /**
     * Sets the status code of the response
     *
     * @param int The HTTP code
     * @return Response
     */
    public function status($code)
    {
        http_response_code($code);

        return $this;
    }

    /**
     * Send a response with JSON
     *
     * @param array The content to send
     * @return void
     */
    public function json($body)
    {
        $this->header("Content-Type", "application/json");
        $this->headers();

        if ($this->settings['pretty_json']) {
            echo json_encode($body, JSON_PRETTY_PRINT);
        } else {
            echo json_encode($body);
        }
    }

    /**
     * A bind for the setcookie PHP func. This allows using only some parameters and not in order.
     *
     * @param string Name of the cookie
     * @param string Value of the cookie
     * @param array Cookie options
     * @return void
     */
    public function cookie($name, $value, $options = [])
    {
        $this->cookies = true;

        // If not defined, create the cookie for this session
        $expire = 0;

        // Use the root of the app as a default path
        $path = '/';

        // Domain of the cookie
        $domain = '';

        // Transmit only with https?
        $secure = false;

        // Prevent js from accesing the cookie
        $httpOnly = false;

        // Replace the settings with the options received
        if (!empty($options)) {
            extract($options);
        }

        setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }

    /**
     * Removes a cookie
     *
     * @param string Name of the cookie
     * @return void
     */
    public function clearCookie($name, $settings = [])
    {
        /**
         * For more info about $settings
         * @see $this->cookie()
         */
        $settings['expire'] = time() - 60 * 60 * 24 * 365;

        $this->cookie($name, '', $settings);
    }

    /**
     * Sends an attachment to force the download of a file
     *
     * @param string Path to the file
     * @param string Name of the file
     * @return void
     */
    public function download($path, $name = '')
    {
        // Clear the existing headers
        $this->headers = array(
            'Content-Type'                => 'application/octet-stream',
            'Content-Transfer-Encoding'    => 'Binary',
            'Content-disposition'        => 'attachment'
        );

        if ($name != '') {
            $this->header('Content-disposition', 'attachment; filename="' . $name . '"');
        }

        // Send headers
        $this->headers();

        // Send content
        die(readfile($path));
    }

    /**
     * Render a template using the configured view engine
     *
     * @param string Path of the template
     * @param array Variables to be put into the view
     * @return void
     */
    public function render($path, $scope = [], $return = false)
    {
        $view = "{$this->settings['views']}/$path";
        $code = $this->engine->render($view, $scope);
        if ($return) {
            return $code;
        }

        echo $code;
        /*
        if ($this->settings['view_engine'] == '') {
            throw new \Exception("There is no engine configured for this view");
        }

        // Path to the template file
        $view = $this->solvePath($this->settings['views'] . '/' . $path);

        // Mustache needs the actual content of the file, so fetch it
        if ($this->settings['view_engine'] == 'mustache') {
            $view = file_get_contents($view);
        }

        if ($this->settings['allow_php']) {
            $code = $this->declare($scope) . '?>' . $this->engine->render($view, $scope);

            if ($return) {
                return $code;
            }

            eval($code);
        } else {
            $code = $this->engine->render($view, $scope);

            if ($return) {
                return $code;
            }

            echo $code;
        }
        */

        // Stop everything else
        exit;
    }


    /**
     * Redirects to a location using Location header
     *
     * @param string URL
     * @return void
     */
    public function location($url)
    {
        header('Location: ' . $url);

        // Stop once redirected
        exit;
    }

    /**
     * Redirects to a location using a http redirect
     *
     * @param string URL
     * @param bool Is a permanent redirect?
     * @return void
     */
    public function redirect($url, $permanent = false)
    {
        $code = ($permanent) ? 301 : 302;

        header('Location: ' . $url, true, $code);

        // Stop once redirected
        exit;
    }

    /**
     * Send the response headers
     *
     * @return void
     */
    private function headers()
    {
        if (headers_sent() && !$this->cookies) {
            return;
        }

        foreach ($this->headers as $header => $content) {
            header($header . ': ' . $content);
        }
    }

    /**
     * Sets a header
     *
     * @param string header
     * @param string content
     * @return void
     */
    public function header($header, $content)
    {
        $this->headers[$header] = $content;
    }

    /**
     * Send a response with a String
     *
     * @param string Response
     * @return void
     */
    public function send($body)
    {
        $this->headers();

        echo $body;
    }
}


/**
 * Generates the routing map to be handled
 *
 */

class Router
{
    /**
     * An array containing the method, path and the list of handlers
     * @var array
     */
    public $map = [];

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        // $this->map = array(
        //     'POST'  => [],
        //     'GET'   => [],
        //     'PUT'   => [],
        //     'DELETE'    => [],
        //     'PATCH' => [],
        //     '*'     => []
        // );
    }

    /**
     * Adds a handler for a route
     *
     * @param string The route to be handled
     * @param function The function to be executed when the route and method matches
     * @param string The method (POST, PUT, ...)
     * @return void
     */
    public function use($mixed, $callback = null, $method = '*')
    {

        if (is_callable($mixed)) {
            $callback = $mixed;
            $route = "";
        } else {
            $route = $mixed;
        }

        // Handle a call with a router
        if ($callback instanceof Router) {
            // TODO
            // $routes = $callback->getRoutes();

            // foreach ($routes as $method => $handlers) {
            //     foreach ($handlers as $path => $handler) {
            //         $path = $route . $path;

            //         if (!isset($this->map[$method][$path])) {
            //             $this->map[$method][$path] = [];
            //         }

            //         if (!is_array($handler)) {
            //             $handler = array($handler);
            //         }

            //         $this->map[$method][$path] = array_merge($this->map[$method][$path], $handler);
            //     }
            // }
        }
        // Handle a call with a custom handler
        else {
            // if (!isset($this->map[$method][$route])) {
            //     $this->map[$method][$route] = [];
            // }

            // $this->map[$method][] = $callback;
            $this->map[] = [
                'method' => $method,
                'route' => $route,
                'callback' => $callback
            ];
        }
    }

    /**
     * Adds a handler for a route in the GET method
     *
     * @param string The route to be handled
     * @param function The function to be executed when the route and method matches
     * @return void
     */
    public function get($route, $callback = null)
    {
        $this->use($route, $callback, 'GET');
    }

    /**
     * Adds a handler for a route in the POST method
     *
     * @param string The route to be handled
     * @param function The function to be executed when the route and method matches
     * @return void
     */
    public function post($route, $callback = null)
    {
        $this->use($route, $callback, 'POST');
    }

    /**
     * Adds a handler for a route in the PUT method
     *
     * @param string The route to be handled
     * @param function The function to be executed when the route and method matches
     * @return void
     */
    public function put($route, $callback = null)
    {
        $this->use($route, $callback, 'PUT');
    }

    /**
     * Adds a handler for a route in the DELETE method
     *
     * @param string The route to be handled
     * @param function The function to be executed when the route and method matches
     * @return void
     */
    public function delete($route, $callback = null)
    {
        $this->use($route, $callback, 'DELETE');
    }

    /**
     * Adds a handler for a route in the PATCH method
     *
     * @param string The route to be handled
     * @param function The function to be executed when the route and method matches
     * @return void
     */
    public function patch($route, $callback = null)
    {
        $this->use($route, $callback, 'PATCH');
    }

    /**
     * Returns the current mapping
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->map;
    }
}
