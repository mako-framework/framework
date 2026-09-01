<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\request;

use Countable;
use IteratorAggregate;
use mako\utility\Arr;
use Override;
use Traversable;

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
	 */
	public function __construct(
		protected array $parameters = []
	) {
	}

	/**
	 * Returns the numner of items in the collection.
	 */
	#[Override]
	public function count(): int
	{
		return count($this->parameters);
	}

	/**
	 * Retruns an array iterator object.
	 */
	#[Override]
	public function getIterator(): Traversable
	{
		yield from $this->parameters;
	}

	/**
	 * Adds a parameter.
	 */
	public function add(string $name, mixed $value): void
	{
		Arr::set($this->parameters, $name, $value);
	}

	/**
	 * Returns true if the parameter exists and false if not.
	 */
	public function has(string $name): bool
	{
		return Arr::has($this->parameters, $name);
	}

	/**
	 * Gets a parameter value.
	 */
	public function get(string $name, mixed $default = null): mixed
	{
		return Arr::get($this->parameters, $name, $default);
	}

	/**
	 * Removes a parameter.
	 */
	public function remove(string $name): void
	{
		Arr::delete($this->parameters, $name);
	}

	/**
	 * Returns all the parameters.
	 */
	public function all(): array
	{
		return $this->parameters;
	}

	/**
	 * Returns request data where keys not in the whitelist have been removed.
	 */
	public function whitelisted(array $keys, array $defaults = []): array
	{
		return array_intersect_key($this->parameters, array_flip($keys)) + $defaults;
	}

	/**
	 * Returns request data where keys in the blacklist have been removed.
	 */
	public function blacklisted(array $keys, array $defaults = []): array
	{
		return array_diff_key($this->parameters, array_flip($keys)) + $defaults;
	}
}
