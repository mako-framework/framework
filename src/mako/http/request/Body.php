<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\request;

use mako\http\request\Parameters;

/**
 * Body.
 *
 * @author Frederic G. Østby
 */
class Body extends Parameters
{
	/**
	 * Constructor.
	 *
	 * @param string $rawBody     Raw request body
	 * @param string $contentType Content type
	 */
	public function __construct(string $rawBody, string $contentType)
	{
		parent::__construct($this->parseBody($rawBody, $contentType));
	}

	/**
	 * Converts the request body into an associative array.
	 *
	 * @param  string $rawBody     Raw request body
	 * @param  string $contentType Content type
	 * @return array
	 */
	protected function parseBody($rawBody, $contentType): array
	{
		if($contentType === 'application/x-www-form-urlencoded')
		{
			$parsed = [];

			parse_str($rawBody, $parsed);

			return $parsed;
		}
		elseif($contentType === 'application/json' || $contentType === 'text/json')
		{
			return json_decode($rawBody, true) ?? [];
		}

		return [];
	}
}
