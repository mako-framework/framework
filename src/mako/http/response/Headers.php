<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Headers.
 *
 * @author Frederic G. Østby
 */
class Headers implements Countable, IteratorAggregate
{
	/**
	 * Headers.
	 *
	 * @var array
	 */
	protected $headers = [];

	/**
	 * Returns the numner of headers.
	 *
	 * @return int
	 */
	public function count(): int
	{
		return count($this->headers);
	}

	/**
	 * Retruns an array iterator object.
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->all());
	}

	/**
	 * Normalizes header names.
	 *
	 * @param  string $name Header name
	 * @return string
	 */
	protected function normalizeName(string $name): string
	{
		return strtolower($name);
	}

	/**
	 * Adds a response header.
	 *
	 * @param string $name    Header name
	 * @param string $value   Header value
	 * @param bool   $replace Replace header?
	 */
	public function add(string $name, string $value, bool $replace = true)
	{
		$normalizedName = $this->normalizeName($name);

		$this->headers[$normalizedName]['name'] = $name;

		if($replace === true)
		{
			$this->headers[$normalizedName]['value'] = [$value];
		}
		else
		{
			$headers = $this->headers[$normalizedName]['value'] ?? [];

			$this->headers[$normalizedName]['value'] = array_merge($headers, [$value]);
		}
	}

	/**
	 * Returns true if the header exists and false if not.
	 *
	 * @param  string $name Header name
	 * @return bool
	 */
	public function has(string $name): bool
	{
		return isset($this->headers[$this->normalizeName($name)]);
	}

	/**
	 * Removes a header.
	 *
	 * @param string $name Header name
	 */
	public function remove(string $name)
	{
		unset($this->headers[$this->normalizeName($name)]);
	}

	/**
	 * Returns all the headers.
	 *
	 * @return array
	 */
	public function all(): array
	{
		return array_column($this->headers, 'value', 'name');
	}

	/**
	 * Clears all the headers.
	 */
	public function clear()
	{
		$this->headers = [];
	}
}
