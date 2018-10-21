<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response;

use ArrayIterator;
use Countable;
use IteratorAggregate;

use function array_column;
use function array_merge;
use function count;
use function strtolower;

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
	 * @param  string                      $name    Header name
	 * @param  string                      $value   Header value
	 * @param  bool                        $replace Replace header?
	 * @return \mako\http\response\Headers
	 */
	public function add(string $name, string $value, bool $replace = true): Headers
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

		return $this;
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
	 * @param  string                      $name Header name
	 * @return \mako\http\response\Headers
	 */
	public function remove(string $name): Headers
	{
		unset($this->headers[$this->normalizeName($name)]);

		return $this;
	}

	/**
	 * Clears all the headers.
	 *
	 * @return \mako\http\response\Headers
	 */
	public function clear(): Headers
	{
		$this->headers = [];

		return $this;
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
}
