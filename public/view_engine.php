<?php

// Set custom template engine
$app->set('view_engine', include __DIR__ . '/lib/CxTemplate.php');

// Or Mustache
// $app->set('view engine','mustache');

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
	$res->render('index.php', [
		'title'	=> "Your website",
		// 'items' => $users,
		'main' => $res->render('users.php', ['items' => $users], true)
	]);
});

// Or this
$router->get('/users/:id', function ($req, $res) use ($users) {
	$res->render('index.php', [
		'title'	=> "Your website",
		// 'item'	=> $users[$req->params->id],
		'main' => $res->render('user.php', $users[$req->params->id || 0], true)
	]);

	// Now in your template, you can use `$this->title` to get that variable!
});
