<?php

namespace Cndrsdrmn\HttpLogger\Tests\Feature;

use Cndrsdrmn\HttpLogger\Tests\TestCase;
use Illuminate\Http\UploadedFile;

class HttpLoggerTest extends TestCase
{
	/**
	 * A test get json.
	 *
	 * @return void
	 */
	public function test_get_json()
	{
		$this->getJson('/http-logger-api-get');
		
		$this->assertFileExists($this->fileLogger());
	}
	
	/**
	 * A test post json.
	 *
	 * @return void
	 */
	public function test_post_json()
	{
		$this->postJson('/http-logger-api-post', $this->defaultRequest());
		
		$this->assertFileExists($this->fileLogger());
	}
	
	/**
	 * A test put json.
	 *
	 * @return void
	 */
	public function test_put_json()
	{
		$this->putJson('/http-logger-api-put', $this->defaultRequest());
		
		$this->assertFileExists($this->fileLogger());
	}
	
	/**
	 * Default request payloads.
	 *
	 * @return array
	 */
	protected function defaultRequest(): array
	{
		return [
			'password' => 'foo',
			'password_confirmation' => 'bar',
			'avatar' => UploadedFile::fake()->image('foo.jpg'),
			'items' => [
				[
					'file' => UploadedFile::fake()->create('item1.pdf',512, 'application/pdf'),
					'name' => 'item1',
				],
				[
					'file' => UploadedFile::fake()->create('item2.mp4', 1024, 'application/octet-stream'),
					'name' => 'item2',
				],
				[
					'file' => UploadedFile::fake()->image('item3.png'),
					'name' => 'item3',
				],
				[
					'file' => UploadedFile::fake()->image('item4.png'),
					'name' => 'item4',
				],
			],
		];
	}
}