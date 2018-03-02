<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use RuntimeException;

use mako\security\Signer;

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
	 * @param array $defaults Default option values
	 */
	public function setOptions(array $defaults)
	{
		$this->defaults = $defaults + $this->defaults;
	}

	/**
	 * Adds a unsigned cookie.
	 *
	 * @param string $name    Cookie name
	 * @param string $value   Cookie value
	 * @param int    $ttl     Time to live - if omitted or set to 0 the cookie will expire when the browser closes
	 * @param array  $options Cookie options
	 */
	public function add(string $name, string $value, int $ttl = 0, array $options = [])
	{
		$ttl = ($ttl === 0) ? 0 : (time() + $ttl);

		$this->cookies[$name] = ['name' => $name, 'value' => $value, 'ttl' => $ttl] + $options + $this->defaults;
	}

	/**
	 * Adds a signed cookie.
	 *
	 * @param string $name    Cookie name
	 * @param string $value   Cookie value
	 * @param int    $ttl     Time to live - if omitted or set to 0 the cookie will expire when the browser closes
	 * @param array  $options Cookie options
	 */
	public function addSigned(string $name, string $value, int $ttl = 0, array $options = [])
	{
		if(empty($this->signer))
		{
			throw new RuntimeException('A [ Signer ] instance is required to sign cookies.');
		}

		$this->add($name, $this->signer->sign($value), $ttl, $options);
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
	 * @param string $name Cookie name
	 */
	public function remove(string $name)
	{
		unset($this->cookies[$name]);
	}

	/**
	 * Deletes a cookie.
	 *
	 * @param string $name    Cookie name
	 * @param array  $options Cookie options
	 */
	public function delete(string $name, array $options = [])
	{
		$this->add($name, '', -3600, $options);
	}

	/**
	 * Clears all the cookies.
	 */
	public function clear()
	{
		$this->cookies = [];
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
