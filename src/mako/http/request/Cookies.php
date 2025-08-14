<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\request;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use mako\http\exceptions\HttpException;
use mako\security\Signer;
use Override;

use function count;

/**
 * Cookies.
 */
class Cookies implements Countable, IteratorAggregate
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected array $cookies = [],
		protected ?Signer $signer = null
	) {
	}

	/**
	 * Returns the numner of cookies.
	 */
	#[Override]
	public function count(): int
	{
		return count($this->cookies);
	}

	/**
	 * Retruns an array iterator object.
	 */
	#[Override]
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->cookies);
	}

	/**
	 * Adds a cookie.
	 */
	public function add(string $name, string $value): void
	{
		$this->cookies[$name] = $value;
	}

	/**
	 * Adds a signed cookie.
	 */
	public function addSigned(string $name, string $value): void
	{
		if (empty($this->signer)) {
			throw new HttpException('A [ Signer ] instance is required to add signed cookies.');
		}

		$this->cookies[$name] = $this->signer->sign($value);
	}

	/**
	 * Returns TRUE if the cookie exists and FALSE if not.
	 */
	public function has(string $name): bool
	{
		return isset($this->cookies[$name]);
	}

	/**
	 * Gets a cookie value.
	 */
	public function get(string $name, mixed $default = null): mixed
	{
		return $this->cookies[$name] ?? $default;
	}

	/**
	 * Gets a signed cookie value.
	 */
	public function getSigned(string $name, mixed $default = null): mixed
	{
		if (empty($this->signer)) {
			throw new HttpException('A [ Signer ] instance is required to read signed cookies.');
		}

		if (isset($this->cookies[$name]) && ($cookie = $this->signer->validate($this->cookies[$name])) !== false) {
			return $cookie;
		}

		return $default;
	}

	/**
	 * Removes a cookie.
	 */
	public function remove(string $name): void
	{
		unset($this->cookies[$name]);
	}

	/**
	 * Returns all the cookies.
	 */
	public function all(): array
	{
		return $this->cookies;
	}
}
