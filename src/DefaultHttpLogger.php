<?php

namespace Cndrsdrmn\HttpLogger;

use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class DefaultHttpLogger implements HttpLoggable
{
	/**
	 * Configuration for http logger.
	 *
	 * @var array
	 */
	protected $config;
	
	/**
	 * Instance of Logger.
	 *
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;
	
	/**
	 * Create a new instance.
	 *
	 * @param  array $config
	 * @return void
	 */
	public function __construct(array $config)
	{
		$this->config = $config;
		$this->logger = Log::channel($this->config['channel']);
	}
	
	/**
	 * Mapping files into array format.
	 *
	 * @param  \Illuminate\Http\UploadedFile[]|\Illuminate\Http\UploadedFile $file
	 * @return array
	 */
	public function mapFiles($file): array
	{
		if ($file instanceof UploadedFile) {
			return [
				'fileName' => $file->getClientOriginalName(),
				'fileSize' => $file->getSize(),
				'extension' => $file->getClientOriginalExtension(),
			];
		}
		
		return array_map([$this, 'mapFiles'], $file);
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function write($request, $response, float $interval): void
	{
		if (! $this->ensureSkipped($request)) {
			$message = $this->formatter($request, $response, $interval);
			
			$this->logger->log($this->forLevel($response), $message);
		}
	}
	
	/**
	 * Make sure a request needs to skip.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @return bool
	 */
	protected function ensureSkipped($request): bool
	{
		return $this->skippedByIp($request->ip());
	}
	
	/**
	 * Retrieve level for response.
	 *
	 * @param  \Illuminate\Http\Response $response
	 * @return string
	 */
	protected function forLevel($response): string
	{
		if ($response->isSuccessful()) {
			return static::LEVEL_DEBUG;
		} else if ($response->isRedirection()) {
			return static::LEVEL_INFO;
		} else if ($response->isClientError()) {
			return static::LEVEL_ERROR;
		}
		
		return static::LEVEL_CRITICAL;
	}
	
	/**
	 * Format a messages.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Illuminate\Http\Response $response
	 * @param  float                     $interval
	 * @return string
	 */
	protected function formatter($request, $response, float $interval): string
	{
		$files = Collection::make(iterator_to_array($request->files))
			->map([$this, 'mapFiles'])
			->all();
		
		$masking = array_map(fn ($value) => Str::mask($value, '*', 0), $request->only($this->config['masking']));
		
		return json_encode([
			'INTERVAL' => $this->intervalForHumans($interval),
			'BASE_URI' => $request->getSchemeAndHttpHost(),
			'ENDPOINT' => $request->getPathInfo(),
			'IP' => $request->ip(),
			'METHOD' => $request->getMethod(),
			'STATUS_CODE' => $statusCode = $response->getStatusCode(),
			'STATUS_TEXT' => Response::$statusTexts[$statusCode] ?? '',
			'REQUEST' => array_merge($request->request->all(), $masking),
			'REQUEST_FILES' => $files,
			'REQUEST_HEADERS' => $request->headers->all(),
			'REQUEST_QUERY' => $request->query->all(),
			'RESPONSE' => $this->resolver($response),
			'RESPONSE_HEADERS' => $response->headers->all(),
		]);
	}
	
	/**
	 * Parse interval for humans format.
	 *
	 * @param  float $interval
	 * @return string
	 */
	protected function intervalForHumans(float $interval): string
	{
		return Carbon::now()->addSeconds($interval)->diffForHumans(['syntax' => CarbonInterface::DIFF_ABSOLUTE]);
	}
	
	/**
	 * Resolver responses.
	 *
	 * @param  \Illuminate\Http\Response $response
	 * @return mixed
	 */
	protected function resolver($response)
	{
		if ($response instanceof JsonResponse) {
			return $response->getData(true);
		}
		
		return $response;
	}
	
	/**
	 * Skipped by IP address.
	 *
	 * @param  string $value
	 * @return bool
	 */
	protected function skippedByIp(string $value): bool
	{
		return collect($this->config['skip_ips'])
			->filter(function ($ip) use ($value) {
				if (Str::contains($ip, '*')) {
					return Str::startsWith($value, trim(str_replace('*', '', $ip), '.'));
				}
				
				return $ip === $value;
			})
			->isNotEmpty();
	}
}