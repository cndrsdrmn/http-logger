<?php

namespace Cndrsdrmn\HttpLogger\Tests\Unit;

use Cndrsdrmn\HttpLogger\DefaultHttpLogger;
use Cndrsdrmn\HttpLogger\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HttpLoggerTest extends TestCase
{
	/**
	 * A test skipped endpoints write log.
	 *
	 * @return void
	 */
	public function test_skipped_endpoints_write_log()
	{
		$configs = ['skip_endpoints' => ['/foo/*']];
		$request = Request::create('foo/not-important', 'POST', ['foo' => 'bar']);
		
		$this->write($request, null, $configs);
		
		$this->assertFileDoesNotExist($this->fileLogger());
	}
	
	/**
	 * A test skipped ips write log.
	 *
	 * @return void
	 */
	public function test_skipped_ips_write_log()
	{
		$configs = ['skip_ips' => ['192.168.*']];
		$request = Request::create('not-important', 'POST', ['foo' => 'bar'], [], [], [
			'REMOTE_ADDR' => '192.168.1.1',
		]);
		
		$this->write($request, null, $configs);
		
		$this->assertFileDoesNotExist($this->fileLogger());
	}
	
	/**
	 * A test write log.
	 *
	 * @return void
	 */
	public function test_write_log()
	{
		$this->write(Request::create('not-important', 'POST', ['foo' => 'bar']));
		
		$this->assertStringContainsString('"foo":"bar"', $this->readFileLogger());
	}
	
	/**
	 * Write default http logger.
	 *
	 * @param  \Illuminate\Http\Request|null  $request
	 * @param  \Illuminate\Http\Response|null $response
	 * @param  array                          $configs
	 * @return void
	 */
	protected function write(Request $request = null, Response $response = null, array $configs = [])
	{
		$configs = array_merge($this->app->config->get('http-logger'), $configs);
		
		$start = microtime(true);
		
		$request  = $request ?? Request::create('not-important');
		$response = $response ?? new Response();
		
		$interval = microtime(true) - $start;
		
		$logger = new DefaultHttpLogger($configs);
		$logger->write($request, $response, $interval);
	}
}