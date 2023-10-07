<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\request;

use function in_array;
use function strpos;
use function substr;

/**
 * Server.
 */
class Server extends Parameters
{
	/**
	 * Returns all the request headers.
	 */
	public function getHeaders(): array
	{
		$headers = [];

		foreach ($this->parameters as $key => $value) {
			if (strpos($key, 'HTTP_') === 0) {
				$headers[substr($key, 5)] = $value;
			}
			elseif (in_array($key, ['CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE'])) {
				$headers[$key] = $value;
			}
		}

		return $headers;
	}
}
