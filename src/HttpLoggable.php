<?php

namespace Cndrsdrmn\HttpLogger;

interface HttpLoggable
{
	/**
	 * Indicate the level of log as critical.
	 *
	 * @var string
	 */
	const LEVEL_CRITICAL = 'critical';
	
	/**
	 * Indicate the level of log as debug.
	 *
	 * @var string
	 */
	const LEVEL_DEBUG = 'debug';
	
	/**
	 * Indicate the level of log as error.
	 *
	 * @var string
	 */
	const LEVEL_ERROR = 'error';
	
	/**
	 * Indicate the level of log as info.
	 *
	 * @var string
	 */
	const LEVEL_INFO = 'info';
	
	/**
	 * Writing a http logger.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Illuminate\Http\Response $response
	 * @param  float                     $interval
	 * @return void
	 */
	public function write($request, $response, float $interval): void;
}