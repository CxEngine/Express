# CxExpress
> Cx Express is a fast and lightweight framework inspired by [ExpressJS framework](https://www.npmjs.com/package/express) for PHP.

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

**TIP**: There are a few more examples in the `index.php` file in this repository.

## Template engines
You have avaible [Pug](https://pugjs.org) (ex Jade) and [Mustache](https://mustache.github.io/). Here's an example:

```php
$app->set('view_engine', include __DIR__ . '/YourTemplateEngine.php');

// Configure the engine to Pug
$app->set('view engine','pug');

// Jade was renamed to Pug, but we recognize it ;)
$app->set('view engine','jade');

// Or Mustache
$app->set('view engine','mustache');

// Set the path to the template files
$app->set('views','./views/pug');

// Now you can do something like this
$router->get('/', function($req, $res) {
	$res->render('index.jade');
});

// Or this
$router->get('/users/:username', function($req, $res) {
	$res->render('index.jade', array(
		'name'	=> $req->params->username
	));

	// Now in jade, you can use #{name} to get that variable!
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
