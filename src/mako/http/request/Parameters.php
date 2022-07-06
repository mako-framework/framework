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

use function array_diff_key;
use function array_flip;
use function array_intersect_key;
use function count;

/**
 * Parameters.
 */
class Parameters implements Countable, IteratorAggregate
{
	/**
	 * Constructor.
	 *
	 * @param array $parameters Parameters
	 */
	public function __construct(
		protected array $parameters = []
	)
	{}

	/**
	 * Returns the numner of items in the collection.
	 *
	 * @return int
	 */
	public function count(): int
	{
		return count($this->parameters);
	}

	/**
	 * Retruns an array iterator object.
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->parameters);
	}

	/**
	 * Adds a parameter.
	 *
	 * @param string $name  Parameter name
	 * @param mixed  $value Parameter value
	 */
	public function add(string $name, mixed $value): void
	{
		Arr::set($this->parameters, $name, $value);
	}

	/**
	 * Returns TRUE if the parameter exists and FALSE if not.
	 *
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
	 * @param  string $name    Parameter name
	 * @param  mixed  $default Default value
	 * @return mixed
	 */
	public function get(string $name, mixed $default = null): mixed
	{
		return Arr::get($this->parameters, $name, $default);
	}

	/**
	 * Removes a parameter.
	 *
	 * @param string $name Parameter name
	 */
	public function remove(string $name): void
	{
		Arr::delete($this->parameters, $name);
	}

	/**
	 * Returns all the parameters.
	 *
	 * @return array
	 */
	public function all(): array
	{
		return $this->parameters;
	}

	/**
	 * Returns request data where keys not in the whitelist have been removed.
	 *
	 * @param  array $keys     Keys to whitelist
	 * @param  array $defaults Default values
	 * @return array
	 */
	public function whitelisted(array $keys, array $defaults = []): array
	{
		return array_intersect_key($this->parameters, array_flip($keys)) + $defaults;
	}

	/**
	 * Returns request data where keys in the blacklist have been removed.
	 *
	 * @param  array $keys     Keys to whitelist
	 * @param  array $defaults Default values
	 * @return array
	 */
	public function blacklisted(array $keys, array $defaults = []): array
	{
		return array_diff_key($this->parameters, array_flip($keys)) + $defaults;
	}
}
