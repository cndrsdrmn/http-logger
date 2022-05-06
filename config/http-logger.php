<?php

return [
	
	/*
	 |-----------------------------------------------------------
	 | HTTP Logger Channel
	 |-----------------------------------------------------------
	 */
	'channel' => env('HTTP_LOGGER_CHANNEL', env('LOG_CHANNEL', 'stack')),
	
	/*
	 |-----------------------------------------------------------
	 | Masking Fields
	 |-----------------------------------------------------------
	 |
	 | Sometimes you need to keep field values secretly.
	 | You can register a field on this "masking" key to keep its value secret.
	 */
	'masking' => [
		'password',
		'password_confirmation',
	],
	
	/*
	 |-----------------------------------------------------------
	 | Skip Endpoints
	 |-----------------------------------------------------------
	 |
	 | Sometimes, you need to skip recording a log for whitelist endpoints.
	 | Example: '/foo/bar', '/foo/*'
	 */
	'skip_endpoints' => [],
	
	/*
	 |-----------------------------------------------------------
	 | Skip IPs Address
	 |-----------------------------------------------------------
	 |
	 | Sometimes, you need to skip recording a log for whitelist IPs address.
	 | Example: '192.168.0.10', '172.10.0.*', '172.9.*',
	 */
	'skip_ips' => [],
];