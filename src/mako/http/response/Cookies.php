<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use mako\security\Signer;
use RuntimeException;

/**
 * Cookies.
 *
 * @author Frederic G. Østby
 */
class Cookies implements Countable, IteratorAggregate
{
	/**
	 * Default options.
	 *
	 * @var array
	 */
	protected $defaults =
	[
		'path'     => '/',
		'domain'    => '',
		'secure'   => false,
		'httponly' => false,
	];

	/**
	 * Cookies.
	 *
	 * @var array
	 */
	protected $cookies = [];

	/**
	 * Signer instance.
	 *
	 * @var \mako\security\Signer
	 */
	protected $signer;

	/**
	 * Constructor.
	 *
	 * @param \mako\security\Signer|null $signer Signer
	 */
	public function __construct(Signer $signer = null)
	{
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
	 * Set default options values.
	 *
	 * @param  array                       $defaults Default option values
	 * @return \mako\http\response\Cookies
	 */
	public function setOptions(array $defaults): Cookies
	{
		$this->defaults = $defaults + $this->defaults;

		return $this;
	}

	/**
	 * Adds a unsigned cookie.
	 *
	 * @param  string                      $name    Cookie name
	 * @param  string                      $value   Cookie value
	 * @param  int                         $ttl     Time to live - if omitted or set to 0 the cookie will expire when the browser closes
	 * @param  array                       $options Cookie options
	 * @return \mako\http\response\Cookies
	 */
	public function add(string $name, string $value, int $ttl = 0, array $options = []): Cookies
	{
		$ttl = ($ttl === 0) ? 0 : (time() + $ttl);

		$this->cookies[$name] = ['name' => $name, 'value' => $value, 'ttl' => $ttl] + $options + $this->defaults;

		return $this;
	}

	/**
	 * Adds a signed cookie.
	 *
	 * @param  string                      $name    Cookie name
	 * @param  string                      $value   Cookie value
	 * @param  int                         $ttl     Time to live - if omitted or set to 0 the cookie will expire when the browser closes
	 * @param  array                       $options Cookie options
	 * @return \mako\http\response\Cookies
	 */
	public function addSigned(string $name, string $value, int $ttl = 0, array $options = []): Cookies
	{
		if(empty($this->signer))
		{
			throw new RuntimeException('A [ Signer ] instance is required to sign cookies.');
		}

		return $this->add($name, $this->signer->sign($value), $ttl, $options);
	}

	/**
	 * Returns true if the cookie exists and false if not.
	 *
	 * @param  string $name Cookie name
	 * @return bool
	 */
	public function has(string $name): bool
	{
		return isset($this->cookies[$name]);
	}

	/**
	 * Removes a cookie.
	 *
	 * @param  string                      $name Cookie name
	 * @return \mako\http\response\Cookies
	 */
	public function remove(string $name): Cookies
	{
		unset($this->cookies[$name]);

		return $this;
	}

	/**
	 * Deletes a cookie.
	 *
	 * @param  string                      $name    Cookie name
	 * @param  array                       $options Cookie options
	 * @return \mako\http\response\Cookies
	 */
	public function delete(string $name, array $options = []): Cookies
	{
		$this->add($name, '', -3600, $options);

		return $this;
	}

	/**
	 * Clears all the cookies.
	 *
	 * @return \mako\http\response\Cookies
	 */
	public function clear(): Cookies
	{
		$this->cookies = [];

		return $this;
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
