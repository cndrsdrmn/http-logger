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
];