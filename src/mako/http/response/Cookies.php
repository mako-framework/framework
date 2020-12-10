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

use function count;
use function time;

/**
 * Cookies.
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
		'domain'   => '',
		'secure'   => false,
		'httponly' => false,
		'samesite' => 'Lax',
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
	public function __construct(?Signer $signer = null)
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
	 * @param  bool                        $raw     Set the cookie without urlencoding the value?
	 * @return \mako\http\response\Cookies
	 */
	public function add(string $name, string $value, int $ttl = 0, array $options = [], bool $raw = false): Cookies
	{
		$expires = ($ttl === 0) ? 0 : (time() + $ttl);

		$this->cookies[$name] =
		[
			'raw'     => $raw,
			'name'    => $name,
			'value'   => $value,
			'options' => ['expires' => $expires] + $options + $this->defaults,
		];

		return $this;
	}

	/**
	 * Adds a raw unsigned cookie.
	 *
	 * @param  string                      $name    Cookie name
	 * @param  string                      $value   Cookie value
	 * @param  int                         $ttl     Time to live - if omitted or set to 0 the cookie will expire when the browser closes
	 * @param  array                       $options Cookie options
	 * @return \mako\http\response\Cookies
	 */
	public function addRaw(string $name, string $value, int $ttl = 0, array $options = []): Cookies
	{
		return $this->add($name, $value, $ttl, $options, true);
	}

	/**
	 * Adds a signed cookie.
	 *
	 * @param  string                      $name    Cookie name
	 * @param  string                      $value   Cookie value
	 * @param  int                         $ttl     Time to live - if omitted or set to 0 the cookie will expire when the browser closes
	 * @param  array                       $options Cookie options
	 * @param  bool                        $raw     Set the cookie without urlencoding the value?
	 * @return \mako\http\response\Cookies
	 */
	public function addSigned(string $name, string $value, int $ttl = 0, array $options = [], bool $raw = false): Cookies
	{
		if(empty($this->signer))
		{
			throw new RuntimeException('A [ Signer ] instance is required to sign cookies.');
		}

		return $this->add($name, $this->signer->sign($value), $ttl, $options, $raw);
	}

	/**
	 * Adds a raw signed cookie.
	 *
	 * @param  string                      $name    Cookie name
	 * @param  string                      $value   Cookie value
	 * @param  int                         $ttl     Time to live - if omitted or set to 0 the cookie will expire when the browser closes
	 * @param  array                       $options Cookie options
	 * @return \mako\http\response\Cookies
	 */
	public function addRawSigned(string $name, string $value, int $ttl, array $options = []): Cookies
	{
		return $this->addSigned($name, $value, $ttl, $options, true);
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
