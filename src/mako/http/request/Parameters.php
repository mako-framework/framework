<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\request;

use ArrayIterator;
use Countable;
use IteratorAggregate;

use mako\utility\Arr;

/**
 * Parameters.
 *
 * @author Frederic G. Østby
 */
class Parameters implements Countable, IteratorAggregate
{
	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @param array $parameters Parameters
	 */
	public function __construct(array $parameters = [])
	{
		$this->parameters = $parameters;
	}

	/**
	 * Returns the numner of items in the collection.
	 *
	 * @access public
	 * @return int
	 */
	public function count(): int
	{
		return count($this->parameters);
	}

	/**
	 * Retruns an array iterator object.
	 *
	 * @access public
	 * @return \ArrayIterator
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->parameters);
	}

	/**
	 * Adds a parameter.
	 *
	 * @access public
	 * @param string $name  Parameter name
	 * @param mixed  $value Parameter value
	 */
	public function add(string $name, $value)
	{
		Arr::set($this->parameters, $name, $value);
	}

	/**
	 * Returns true if the parameter exists and false if not.
	 *
	 * @access public
	 * @param  string $name Parameter name
	 * @return bool
	 */
	public function has(string $name): bool
	{
		return Arr::has($this->parameters, $name);
	}

	/**
	 * Gets a parameter value.
	 *
	 * @access public
	 * @param  string     $name    Parameter name
	 * @param  null|mixed $default Default value
	 * @return null|mixed
	 */
	public function get(string $name, $default = null)
	{
		return Arr::get($this->parameters, $name, $default);
	}

	/**
	 * Removes a parameter.
	 *
	 * @access public
	 * @param string $name Parameter name
	 */
	public function remove(string $name)
	{
		Arr::delete($this->parameters, $name);
	}

	/**
	 * Returns all the parameters.
	 *
	 * @access public
	 * @return array
	 */
	public function all(): array
	{
		return $this->parameters;
	}
}
