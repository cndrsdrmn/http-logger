<?php

namespace Cndrsdrmn\HttpLogger\Middleware;

use Closure;
use Cndrsdrmn\HttpLogger\HttpLoggable;

class HttpLogger
{
	/**
	 * Instance of HttpLogger.
	 *
	 * @var \Cndrsdrmn\HttpLogger\HttpLoggable
	 */
	protected $logger;
	
	/**
	 * Create a new instance.
	 *
	 * @param  \Cndrsdrmn\HttpLogger\HttpLoggable $logger
	 * @return void
	 */
	public function __construct(HttpLoggable $logger)
	{
		$this->logger = $logger;
	}
	
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
	 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
	 */
	public function handle($request, Closure $next)
	{
		$start = microtime(true);
		
		$response = $next($request);
		$interval = microtime(true) - $start;
		
		$this->logger->write($request, $response, $interval);
		
		return $response;
	}
}