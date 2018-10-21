<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing\middleware;

use function array_key_exists;

/**
 * Base middleware.
 *
 * @author Frederic G. Østby
 */
abstract class Middleware implements MiddlewareInterface
{
	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = [];

	/**
	 * {@inheritdoc}
	 */
	public function setParameters(array $parameters)
	{
		$this->parameters = $parameters;
	}

	/**
	 * Returns the parameter value.
	 *
	 * @param  int|string $key     Parameter key
	 * @param  mixed      $default Default value to return if parameter doesn't exist
	 * @return mixed
	 */
	protected function getParameter($key = 0, $default = null)
	{
		if(array_key_exists($key, $this->parameters))
		{
			return $this->parameters[$key];
		}

		return $default;
	}
}
