<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing;

use RuntimeException;

/**
 * Middleware collection.
 *
 * @author Frederic G. Østby
 */
class Middleware
{
	/**
	 * Default priority.
	 *
	 * @var int
	 */
	const DEFAULT_PRIORITY = 100;

	/**
	 * Middleware priority.
	 *
	 * @var array
	 */
	protected $priority = [];

	/**
	 * Registered middleware.
	 *
	 * @var array
	 */
	protected $middleware = [];

	/**
	 * Sets the middleware priority.
	 *
	 * @param  array                         $priority Middleware priority
	 * @return \mako\http\routing\Middleware
	 */
	public function setPriority(array $priority)
	{
		$this->priority = $priority + $this->priority;

		return $this;
	}

	/**
	 * Resets middleware priority.
	 *
	 * @return \mako\http\routing\Middleware
	 */
	public function resetPriority()
	{
		$this->priority = [];

		return $this;
	}

	/**
	 * Orders resolved middleware by priority.
	 *
	 * @param  array $middleware Array of middleware
	 * @return array
	 */
	public function orderByPriority(array $middleware): array
	{
		if(empty($this->priority))
		{
			return $middleware;
		}

		$priority = array_intersect_key($this->priority, $middleware) + array_fill_keys(array_keys(array_diff_key($middleware, $this->priority)), static::DEFAULT_PRIORITY);

		// Sort the priority map using stable sorting

		$position = 0;

		foreach($priority as $key => $value)
		{
			$priority[$key] = [$position++, $value];
		}

		uasort($priority, function($a, $b)
		{
			return ($a[1] === $b[1]) ? ($a[0] > $b[0]) : (($a[1] > $b[1]) ? 1 : -1);
		});

		foreach($priority as $key => $value)
		{
			$priority[$key] = $value[1];
		}

		// Return sorted middleware list

		return array_merge($priority, $middleware);
	}

	/**
	 * Adds a middleware.
	 *
	 * @param  string                        $name       Middleware name
	 * @param  string                        $middleware Middleware class name
	 * @return \mako\http\routing\Middleware
	 */
	public function register(string $name, string $middleware)
	{
		$this->middleware[$name] = $middleware;

		return $this;
	}

	/**
	 * Returns the chosen middleware.
	 *
	 * @param  string $middleware Middleware name
	 * @return string
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
