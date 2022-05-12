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
		return $this->skippedBy('skip_ips', $request->ip()) || $this->skippedBy('skip_endpoints', $request->getPathInfo());
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
		
		return json_encode([
			'INTERVAL' => $this->intervalForHumans($interval),
			'BASE_URI' => $request->getSchemeAndHttpHost(),
			'ENDPOINT' => $request->getPathInfo(),
			'IP' => $request->ip(),
			'METHOD' => $request->getMethod(),
			'STATUS_CODE' => $statusCode = $response->getStatusCode(),
			'STATUS_TEXT' => Response::$statusTexts[$statusCode] ?? '',
			'REQUEST' => $this->masking($request->request->all()),
			'REQUEST_FILES' => $files,
			'REQUEST_HEADERS' => $this->masking($request->headers->all()),
			'REQUEST_QUERY' => $this->masking($request->query->all()),
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
	 * Masks a portion of a string with a repeated character.
	 *
	 * @param  string   $string
	 * @param  string   $character
	 * @param  int      $index
	 * @param  int|null $length
	 * @param  string   $encoding
	 * @return string
	 */
	protected function mask($string, $character, $index, $length = null, $encoding = 'UTF-8'): string
	{
		if ($character === '') {
			return $string;
		}
		
		$segment = mb_substr($string, $index, $length, $encoding);
		
		if ($segment === '') {
			return $string;
		}
		
		$start = mb_substr($string, 0, mb_strpos($string, $segment, 0, $encoding), $encoding);
		$end   = mb_substr($string, mb_strpos($string, $segment, 0, $encoding) + mb_strlen($segment, $encoding));
		
		return $start . str_repeat(mb_substr($character, 0, 1, $encoding), mb_strlen($segment, $encoding)) . $end;
	}
	
	/**
	 * Masking request parameters.
	 *
	 * @param  array $parameters
	 * @return array
	 */
	protected function masking(array $parameters): array
	{
		foreach ($parameters as $key => $value) {
			if (in_array($key, $this->config['masking'])) {
				$parameters[$key] = is_array($value)
					? array_map(fn ($value) => $this->mask($value, '*', 0), $value)
					: $this->mask($value, '*', 0);
			}
		}
		
		return $parameters;
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
	 * Skipped by configurations.
	 *
	 * @param  string $config
	 * @param  string $value
	 * @param  string $trimChars
	 * @return bool
	 */
	protected function skippedBy(string $config, string $value, string $trimChars = " \t\n\r\0\x0B"): bool
	{
		return collect($this->config[$config] ?? [])
			->filter(fn ($skipped) => Str::contains($skipped, '*')
				? Str::startsWith($value, trim(str_replace('*', '', $skipped), $trimChars))
				: $skipped === $value
			)
			->isNotEmpty();
	}
}