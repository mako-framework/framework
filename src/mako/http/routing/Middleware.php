<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http\routing;

use RuntimeException;

/**
 * Middleware collection.
 *
 * @author  Frederic G. Østby
 */
class Middleware
{
	/**
	 * Registered middleware.
	 *
	 * @var array
	 */
	protected $middleware = [];

	/**
	 * Adds a middleware.
	 *
	 * @access  public
	 * @param   string   $name       Middleware name
	 * @param   string  $middleware  Middleware class name
	 */
	public function register(string $name, string $middleware)
	{
		$this->middleware[$name] = $middleware;
	}

	/**
	 * Returns the chosen middleware.
	 *
	 * @access  public
	 * @param   string  $middleware  Middleware name
	 * @return  string
	 */
	public function get(string $middleware): string
	{
		if(!isset($this->middleware[$middleware]))
		{
			throw new RuntimeException(vsprintf("%s(): No middleware named [ %s ] has been defined.", [__METHOD__, $middleware]));
		}

		return $this->middleware[$middleware];
	}
}