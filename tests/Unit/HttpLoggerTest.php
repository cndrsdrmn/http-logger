<?php

namespace Cndrsdrmn\HttpLogger\Tests\Unit;

use Cndrsdrmn\HttpLogger\DefaultHttpLogger;
use Cndrsdrmn\HttpLogger\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

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
		$configs = ['masking' => ['foo', 'secret-key', 'access-token']];
		$masking = str_pad('', strlen('') - mb_strlen('') + 64, '*', STR_PAD_RIGHT);
		
		$request = Request::create('not-important', 'POST', [
			'foo' => 'bar',
		], [], [], [
			'HTTP_SECRET_KEY' => Str::random(64),
		]);
		$request->query->set('access-token', Str::random(64));
		
		$this->write($request, null, $configs);
		
		$this->assertStringContainsString(str_replace('?', $masking, '"secret-key":["?"]'), $this->readFileLogger());
		$this->assertStringContainsString(str_replace('?', $masking, '"access-token":"?"'), $this->readFileLogger());
		$this->assertStringContainsString(str_replace('?', '***', '"foo":"?"'), $this->readFileLogger());
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