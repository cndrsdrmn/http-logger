<?php

namespace Cndrsdrmn\HttpLogger;

use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;

class HttpLoggerServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap a http logger services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->setUpConfig();
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function register()
	{
		$this->app->singleton(
			HttpLoggable::class,
			fn ($app) => new DefaultHttpLogger($app->config->get('http-logger'))
		);
		
		$this->mergeConfigFrom($this->configPath(), 'http-logger');
	}
	
	/**
	 * Get config path.
	 *
	 * @return string
	 */
	protected function configPath(): string
	{
		return dirname(__DIR__) . '/config/http-logger.php';
	}
	
	/**
	 * Setup configuration http logger.
	 *
	 * @return void
	 */
	protected function setUpConfig(): void
	{
		if ($this->app->runningInConsole()) {
			if ($this->app instanceof LaravelApplication) {
				$this->publishes([$this->configPath() => config_path('http-logger.php')], 'config');
			} else if ($this->app instanceof LumenApplication) {
				$this->app->configure('sluggable');
			}
		}
	}
}