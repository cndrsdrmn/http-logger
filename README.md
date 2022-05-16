# HTTP Logger

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cndrsdrmn/http-logger.svg)](https://packagist.org/packages/cndrsdrmn/http-logger)
[![Testing](https://github.com/cndrsdrmn/http-logger/actions/workflows/github-ci.yml/badge.svg)](https://github.com/cndrsdrmn/http-logger/actions/workflows/github-ci.yml)
[![GitHub](https://img.shields.io/github/license/cndrsdrmn/http-logger)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/cndrsdrmn/http-logger.svg)](https://packagist.org/packages/cndrsdrmn/http-logger)

This package adds a middleware for writing a log of incoming requests and out-coming responses.

## Installation

Install the package via composer:

```shell
composer require cndrsdrmn/http-logger
```

Optionally you can publish the config file with:

```shell
php artisan vendor:publish --provider="Cndrsdrmn\HttpLogger\HttpLoggerServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [
	
	/*
	 |-----------------------------------------------------------
	 | HTTP Logger Channel
	 |-----------------------------------------------------------
	 */
	'channel' => env('HTTP_LOGGER_CHANNEL', env('LOG_CHANNEL', 'stack')),
	
	/*
	 |-----------------------------------------------------------
	 | Masking Fields
	 |-----------------------------------------------------------
	 |
	 | Sometimes you need to keep field values secretly.
	 | You can register a field on this "masking" key to keep its value secret.
	 | Masked a request "body", "query" and "headers".
	 */
	'masking' => [
	    'password',
	    'password_confirmation',
	],
	
	/*
	 |-----------------------------------------------------------
	 | Skip Endpoints
	 |-----------------------------------------------------------
	 |
	 | Sometimes, you need to skip recording a log for whitelist endpoints.
	 | Example: '/foo/bar', '/foo/*'
	 */
	'skip_endpoints' => [],
	
	/*
	 |-----------------------------------------------------------
	 | Skip IPs Address
	 |-----------------------------------------------------------
	 |
	 | Sometimes, you need to skip recording a log for whitelist IPs address.
	 | Example: '192.168.0.10', '172.10.0.*', '172.9.*',
	 */
	'skip_ips' => [],
];
```

## Configuration

We recommend making a new channel in `config/logging.php` for handling the HTTP Logger.\
By default, we use the `stack` channel for handling this. You can override with put the variable `HTTP_LOGGER_CHANNEL` in the `.env` file.

```php
// in config/logging.php

'channels' => [
    // ...
    'http-logger' => [
        'driver' => 'daily',
        'path' => storage_path('logs/http-loggers/http-logger.log'),
        'level' => 'debug',
        'days' => 14,
    ],
    // ...
]

// in .env file
HTTP_LOGGER_CHANNEL=http-logger
```

For lumen your need enable `withFacades` at `bootstrap/app.php`.

```php
$app->withFacades();
```

## Usage

### 1. Laravel

This package provides a middleware that can be added as a global middleware or as a single route.\
Please see [official documentation](https://laravel.com/docs/9.x/middleware#registering-middleware) for more information.

```php
// in app/Http/Kernel.php
protected $middleware = [
    // ...
    \Cndrsdrmn\HttpLogger\Middleware\HttpLogger::class,
];

// in a routes file
Route::post('foo', function () {
    // action here
})->middleware(\Cndrsdrmn\HttpLogger\Middleware\HttpLogger::class);
```

### 2. Lumen

This package provides a middleware that can be added as a global middleware or as a single route.\
Please see [official documentation](https://lumen.laravel.com/docs/9.x/middleware#registering-middleware) for more information.

```php
// in bootstrap/app.php

// Global Middleware
$app->middleware([
   \Cndrsdrmn\HttpLogger\Middleware\HttpLogger::class,
]);

// OR Assigning Middleware To Routes
$app->routeMiddleware([
    'http-logger' => \Cndrsdrmn\HttpLogger\Middleware\HttpLogger::class,
]);

// in routes file
$router->get('/', ['middleware' => ['http-logger'], function () {
    //
}]);
```

Create/copy a configuration file from [here](config/http-logger.php) into `config/http-logger.php` then registering a configuration at `bootstrap/app.php`.

```php
$app->configure('http-logger');
```

## Testing

```shell
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits
- [Candra Sudirman](https://github.com/cndrsdrmn)
- [All Contributors](https://github.com/cndrsdrmn/http-logger/graphs/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.