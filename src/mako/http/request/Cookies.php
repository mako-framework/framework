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

use function count;

/**
 * Cookies.
 */
class Cookies implements Countable, IteratorAggregate
{
	/**
	 * Cookies.
	 *
	 * @var array
	 */
	protected $cookies;

	/**
	 * Signer.
	 *
	 * @var \mako\security\Signer
	 */
	protected $signer;

	/**
	 * Constructor.
	 *
	 * @param array                      $cookies Cookies
	 * @param \mako\security\Signer|null $signer  Signer
	 */
	public function __construct(array $cookies = [], ?Signer $signer = null)
	{
		$this->cookies = $cookies;

		$this->signer = $signer;
	}

	/**
	 * Returns the numner of cookies.
	 *
	 * @return int
	 */
	public function count(): int
	{
		return count($this->cookies);
	}

	/**
	 * Retruns an array iterator object.
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->cookies);
	}

	/**
	 * Adds a cookie.
	 *
	 * @param string $name  Cookie name
	 * @param string $value Cookie value
	 */
	public function add(string $name, string $value): void
	{
		$this->cookies[$name] = $value;
	}

	/**
	 * Adds a signed cookie.
	 *
	 * @param string $name  Cookie name
	 * @param string $value Cookie value
	 */
	public function addSigned(string $name, string $value): void
	{
		if(empty($this->signer))
		{
			throw new HttpException('A [ Signer ] instance is required to add signed cookies.');
		}

		$this->cookies[$name] = $this->signer->sign($value);
	}

	/**
	 * Returns TRUE if the cookie exists and FALSE if not.
	 *
	 * @param  string $name Cookie name
	 * @return bool
	 */
	public function has(string $name): bool
	{
		return isset($this->cookies[$name]);
	}

	/**
	 * Gets a cookie value.
	 *
	 * @param  string $name    Cookie name
	 * @param  mixed  $default Default value
	 * @return mixed
	 */
	public function get(string $name, $default = null)
	{
		return $this->cookies[$name] ?? $default;
	}

	/**
	 * Gets a signed cookie value.
	 *
	 * @param  string $name    Cookie name
	 * @param  mixed  $default Default value
	 * @return mixed
	 */
	public function getSigned(string $name, $default = null)
	{
		if(empty($this->signer))
		{
			throw new HttpException('A [ Signer ] instance is required to read signed cookies.');
		}

		if(isset($this->cookies[$name]) && ($cookie = $this->signer->validate($this->cookies[$name])) !== false)
		{
			return $cookie;
		}

		return $default;
	}

	/**
	 * Removes a cookie.
	 *
	 * @param string $name Cookie name
	 */
	public function remove(string $name): void
	{
		unset($this->cookies[$name]);
	}

	/**
	 * Returns all the cookies.
	 *
	 * @return array
	 */
	public function all(): array
	{
		return $this->cookies;
	}
}
