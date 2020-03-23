# CxExpress
> Cx Express is a zero dependency and fast PHP framework inspired by [ExpressJS framework](https://www.npmjs.com/package/express). That allows you to quickly develop website or API's.

Docs at https://cxengine.github.io/Express/

# Roadmap
- [x] Handle next routes
- [x] Decouple template engine
- [x] Removed static function, not PHP's business
- [ ] Direct route, since PHP doesnt run as a proces like nodejs
- [ ] Route /0 bug

# Inspired on
Most of this framework has been based on the great work of aeberdinelli (https://github.com/aeberdinelli/express-php). Hereby adding the next route functionality and stripping out the template engine support just like the original Express to keep it as light as possible.

Another great similar project can be found here https://github.com/ahkohd/express-php.


## Install
**Note**: To run ExpressPHP you need PHP >= 7.0 and Apache.

The preferred installation is using Composer:

`composer require cxengine/cxexpress`

## Usage
### Manual
Just drop the single `Express.php` file somewhere in your project.

```php
<?php
include __DIR__.'/lib/Express.php';

$app = new Express();
$router = new Router();

$router->get('/', function($req, $res) {
	$res->send('hello world!');
});

$app->listen($router);
?>
```
<!-- 
### Composer
If you installed using composer, you can just do:

```php
<?php
include __DIR__.'/vendor/autoload.php';

$app = new Express();
$router = new Router();

$router->get('/', function($req, $res) {
	$res->send('hello world!');
});

$app->listen($router);
?>
``` -->

## Routes
Routes are handled using a Router instance, for example:

```php
$router = new Router();
$router->get('/', function($req, $res) {
    // This will be called when someone goes to the main page using GET method.
});
```

You can handle post requests as well using post() instead of get(). Same for put() and delete().

## Route with dynamic parameters
You can route dynamic URL using parameters, for example:

```php
$router = new Router();
$router->get('/:something/:else', function($req, $res) {
    /**
     * Now let's imagine someone enters to URL: /hello/bye, then:
     *
     * $req->params->something will contain 'hello'
     * $req->params->else will contain 'bye'
     */
    $req->send(<<<HTML
        <div class="container">
            <h1>Welcome</h1>
            <p>something: {$req->params->something}</p>
            <p>else: {$req->params->else}</p>
        </div>
    HTML;)
});
```

## Responses
If you're developing an API for example, you can send json simply doing:

```php
$router->post('/', function($req, $res) {
	$res->status(400)->json(array(
		'message'	=> 'Hello'
	));
});
```

You can also send a custom http response code using:

```php
$router->post('/', function($req, $res) {
	$res->status(201)->json({
		'message'	=> 'Created!'
	});
});
```

**TIP**: There are more examples in the `/public` directory in this repository.

### Redirect
```php
$router->get('/', function($req, $res) {
	$res->redirect('/dashboard');
});
```

## Template engines
Here's an example:

```php
<?php

// Set custom template engine
$app->set('view_engine', include __DIR__ . '/lib/CxTemplate.php');

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
```

## Request info
- You have the body of the request in $res->body no matter if you re handling POST or PUT.
- You have the query string under $req->query
- You have the cookies in $req->cookies
- You have all the request headers in $req->headers


# Deployment

## PHP built-in server
Run the following command in a terminal to start localhost web server, assuming ./public/ is public-accessible directory with index.php file:

```
php -S localhost:8888 -t public public/index.php
```

If you are not using `index.php` as your entry point then change appropriately.

## Apache configuration
Ensure your .htaccess and index.php files are in the same public-accessible directory. The .htaccess file should contain this code:
```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

### Running in a subfolder
- Change the basePath in `index.php`:
```
$app->set('basePath', '/yourapp');
```
- Modify the `.htaccess`:
```
RewriteEngine On
RewriteBase /yourapp
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```
