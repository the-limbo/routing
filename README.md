# Getting started with Limbo/Routing
[![Latest Stable Version](http://poser.pugx.org/limbo/container/v)](https://packagist.org/packages/limbo/container)
[![Latest Unstable Version](http://poser.pugx.org/limbo/container/v/unstable)](https://packagist.org/packages/limbo/container)
[![License](http://poser.pugx.org/limbo/container/license)](https://packagist.org/packages/limbo/container)
[![Total Downloads](http://poser.pugx.org/limbo/container/downloads)](https://packagist.org/packages/limbo/container)

Limbo Routing - PSR-7 and PSR-15 implementation routing system based on [nikic/fast-route](https://packagist.org/packages/nikic/fast-route).

* [psr/http-message](https://www.php-fig.org/psr/psr-7/)
* [psr/http-server-handler](https://www.php-fig.org/psr/psr-15/)
* [psr/http-server-middleware](https://www.php-fig.org/psr/psr-15/)

## Features

* Support PSR-7 message implementation (Laminas Diactoros...)
* Support PSR-15 middleware

## Install via [composer](https://getcomposer.org/)

```
composer require limbo/routing
```

### Create router and dispatch Server Request

```php
require_once 'vendor/autoload.php';

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\SapiEmitter;
use Limbo\Container\Container;
use Limbo\Routing\Router;

$container = new Container([
    ...$definitions
]);
$router = new Router($container);
# Add route
$router->get('/controller', 'App\Controllers\HomeController@index');
$router->get('/{id:num}', function(int $id) {
    return new HtmlResponse('My ID: ' . $id);
});
$router->get('/hello', [new App\Controllers\HomeController(), 'index']);
$router->get('/hello-world', [App\Controllers\HomeController::class, 'index']);

$response = $router->handle(ServerRequestFactory::fromGlobals());

(new SapiEmitter)->emit($response);

```
