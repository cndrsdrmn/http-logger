# HTTP Logger

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cndrsdrmn/http-logger.svg)](https://packagist.org/packages/cndrsdrmn/http-logger)
[![Testing](https://github.com/cndrsdrmn/http-logger/actions/workflows/github-ci.yml/badge.svg)](https://github.com/cndrsdrmn/http-logger/actions/workflows/github-ci.yml)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)
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
	 */
	'masking' => [
	    'password',
	    'password_confirmation',
	],
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

## Usage

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

## Testing

```shell
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## License

MIT License. Please see [License File](LICENSE) for more information.