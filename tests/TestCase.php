<?php

namespace Cndrsdrmn\HttpLogger\Tests;

use Cndrsdrmn\HttpLogger\HttpLoggerServiceProvider;
use Cndrsdrmn\HttpLogger\Middleware\HttpLogger;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
	/**
	 * Define file logger.
	 *
	 * @return string
	 */
	public function fileLogger(): string
	{
		return $this->tempPath('http-logger.log');
	}
	
	/**
	 * Initialize directory.
	 *
	 * @param  string $directory
	 * @return void
	 */
	public function initializeDirectory(string $directory): void
	{
		if (File::isDirectory($directory)) {
			File::deleteDirectory($directory);
		}
		
		File::makeDirectory($directory);
	}
	
	/**
	 * Read file logger.
	 *
	 * @return string
	 */
	public function readFileLogger(): string
	{
		return file_get_contents($this->fileLogger());
	}
	
	/**
	 * Define temporary path.
	 *
	 * @param  string $prefix
	 * @return string
	 */
	public function tempPath(string $prefix = ''): string
	{
		$path = trim(__DIR__ . '/temp');
		
		return implode('/', [$path, $prefix]);
	}
	
	/**
	 * {@inheritDoc}
	 */
	protected function defineRoutes($router)
	{
		$router->get('/http-logger-api-get', function () {
			return response()->json(['message' => 'HTTP LOGGER']);
		});
		
		$router->post('/http-logger-api-post', function () {
			return response()->json(['message' => 'HTTP LOGGER']);
		});
		
		$router->put('/http-logger-api-put', function () {
			return response()->noContent();
		});
		
		$router->patch('/http-logger-api-patch', function () {
			return response()->noContent();
		});
		
		$router->delete('/http-logger-api-delete', function () {
			return response()->noContent();
		});
	}
	
	/**
	 * {@inheritDoc}
	 */
	protected function defineWebRoutes($router)
	{
		$router->get('/http-logger-web-get', function () {
			return 'HTTP LOGGER';
		});
		
		$router->post('/http-logger-web-post', function () {
			return redirect()->to('/http-logger-web-get');
		});
		
		$router->put('/http-logger-web-put', function () {
			return redirect()->to('/http-logger-web-get');
		});
		
		$router->patch('/http-logger-web-patch', function () {
			return redirect()->to('/http-logger-web-get');
		});
		
		$router->delete('/http-logger-web-delete', function () {
			return redirect()->to('/http-logger-web-get');
		});
	}
	
	/**
	 * {@inheritDoc}
	 */
	protected function getEnvironmentSetUp($app)
	{
		/** @var \Illuminate\Config\Repository $config */
		$config  = $app->make('config');
		$channel = $config->get('http-logger.channel');
		
		$config->set("logging.channels.{$channel}", [
			'driver' => 'single',
			'path' => $this->fileLogger(),
			'level' => 'debug',
		]);
		
		$config->set('logging.default', $channel);
	}
	
	/**
	 * {@inheritDoc}
	 */
	protected function getPackageProviders($app): array
	{
		return [
			HttpLoggerServiceProvider::class,
		];
	}
	
	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void
	{
		parent::setUp();
		
		$this->initializeDirectory($this->tempPath());
		
		$this->app[Kernel::class]->pushMiddleware(HttpLogger::class);
	}
}