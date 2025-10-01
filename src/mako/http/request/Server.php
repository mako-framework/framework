<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\request;

use function in_array;
use function str_starts_with;
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
			if (str_starts_with($key, 'HTTP_')) {
				$headers[substr($key, 5)] = $value;
			}
			elseif (in_array($key, ['CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE'])) {
				$headers[$key] = $value;
			}
		}

		return $headers;
	}
}
