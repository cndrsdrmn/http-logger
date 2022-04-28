<?php

namespace Cndrsdrmn\HttpLogger\Tests\Unit;

use Cndrsdrmn\HttpLogger\DefaultHttpLogger;
use Cndrsdrmn\HttpLogger\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HttpLoggerTest extends TestCase
{
	/**
	 * Instance of DefaultHttpLogger.
	 *
	 * @var \Cndrsdrmn\HttpLogger\DefaultHttpLogger
	 */
	protected $logger;
	
	/**
	 * A test write log.
	 *
	 * @return void
	 */
	public function test_write_log()
	{
		$start    = microtime(true);
		$request  = Request::create('not-important', 'POST', ['foo' => 'bar']);
		$response = new Response();
		$interval = microtime(true) - $start;
		
		$this->logger->write($request, $response, $interval);
		
		$this->assertStringContainsString('"foo":"bar"', $this->readFileLogger());
	}
	
	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void
	{
		parent::setUp();
		
		$this->logger = new DefaultHttpLogger($this->app->config->get('http-logger'));
	}
}