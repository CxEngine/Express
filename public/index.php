<?php

include __DIR__ . '/../Express.php';

$app = new Express();
$router = new Router();

$app->set('basePath', '');

include __DIR__."/cors.php";
include __DIR__."/view_engine.php";

$router->get('/', function($req, $res) {
    $res->send(<<<HTML
    <h1>hello world!</h1>
    <a href="/about">/about</a>
    <a href="/error">/error</a>
    HTML);
});

$router->get('/error', function($req, $res) {
	throw new Error("Ai");
});

$router->get('/about', function($req, $res) {
	$res->send('Hello');
});

$router->get('/api', function($req, $res) {
	$res->json([
        'message' => "Hello"
    ]);
});

$router->get('/:name', function ($req, $res, $next) {
   echo "Your name: {$req->params->name}";
});

$router->use('/test/:test', function ($req, $res, $next) {
    echo "Hello {$req->params->test}";
    $next();
});

$router->use('/test/:test', function ($req, $res, $next) {
    echo "World {$req->params->test}";
    $next();
});

$router->use(function ($req, $res, $next) {
    $res->send("Not found");
});

try {
    $app->listen($router);
} catch (Throwable $t) {
    http_response_code(400);
    echo "ai\n";
    echo $t;
}


