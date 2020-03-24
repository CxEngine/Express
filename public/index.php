<?php

include __DIR__ . '/../Express.php';

$app = new Express();
$router = new Router();

$app->set('basePath', '');

/**
 * Middleware
 */
$router->use(include __DIR__."/middleware/cors.php");


$router->get('/test', function($req, $res) {
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





/**
 * Example custom view engine
 */

// Set custom template engine
$app->set('view_engine', include __DIR__ . '/plugins/cxtemplate/CxTemplate.php');

// Set the path to the template files
$app->set('views', './views');

// Mock some users
$users = [
	[
		'id' => 0,
		'title' => 'Henk',
		'subtitle' => 'Customer',
		'image' => 'https://via.placeholder.com/150'
	],
	[
		'id' => 1,
		'title' => 'Alex',
		'subtitle' => 'Administrator',
		'image' => 'https://via.placeholder.com/150'
	]
];

// Now you can do something like this
$router->get('/', function ($req, $res) use ($users) {
	$res->render('layout.php', [
		'title'	=> "Homepage",
		// 'items' => $users,
		'main' => $res->render('users.php', ['items' => $users], true)
	]);
});

// Or this
$router->get('/users/:id', function ($req, $res) use ($users) {
	$res->render('layout.php', [
		'title'	=> "Your website",
		// 'item'	=> $users[$req->params->id],
		'main' => $res->render('user.php', $users[$req->params->id || 0], true)
	]);

	// Now in your template, you can use `$title` to get that variable!
});

/**
 * End with a catch all
 */
$router->use(function ($req, $res, $next) {
    $res->send("Not found");
});

/**
 * Handle request
 */
try {
    $app->listen($router);
} catch (Throwable $t) {
    http_response_code(400);
    echo "ai\n";
    echo $t;
}


