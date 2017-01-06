<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\request;

use mako\http\request\Parameters;

/**
 * Server.
 *
 * @author Frederic G. Østby
 */
class Server extends Parameters
{
	/**
	 * Headers.
	 *
	 * @var array
	 */
	protected $headers;

	/**
	 * Returns all the request headers.
	 *
	 * @access protected
	 * @return array
	 */
	protected function collectHeaders(): array
	{
		$headers = [];

		foreach($this->parameters as $key => $value)
		{
			if(strpos($key, 'HTTP_') === 0)
			{
				$headers[substr($key, 5)] = $value;
			}
			elseif(in_array($key, ['CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE']))
			{
				$headers[$key] = $value;
			}
		}

		return $headers;
	}

	/**
	 * Returns the request headers.
	 *
	 * @access public
	 * @return array
	 */
	public function getHeaders(): array
	{
		if(!isset($this->headers))
		{
			$this->headers = $this->collectHeaders();
		}

		return $this->headers;
	}
}
