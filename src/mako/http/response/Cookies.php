<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use mako\http\exceptions\HttpException;
use mako\http\response\traits\PatternMatcherTrait;
use mako\security\Signer;
use Override;

use function array_filter;
use function count;
use function time;

/**
 * Cookies.
 */
class Cookies implements Countable, IteratorAggregate
{
	use PatternMatcherTrait;

	/**
	 * Default options.
	 */
	protected array $defaults = [
		'path'     => '/',
		'domain'   => '',
		'secure'   => false,
		'httponly' => false,
		'samesite' => 'Lax',
	];

	/**
	 * Cookies.
	 */
	protected array $cookies = [];

	/**
	 * Constructor.
	 */
	public function __construct(
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
	 * Set default options values.
	 *
	 * @return $this
	 */
	public function setOptions(array $defaults): Cookies
	{
		$this->defaults = $defaults + $this->defaults;

		return $this;
	}

	/**
	 * Adds a unsigned cookie.
	 *
	 * @return $this
	 */
	public function add(string $name, string $value, int $ttl = 0, array $options = [], bool $raw = false, ?string $group = null): Cookies
	{
		$expires = ($ttl === 0) ? 0 : (time() + $ttl);

		$this->cookies[$name] = [
			'raw'     => $raw,
			'name'    => $name,
			'value'   => $value,
			'group'   => $group,
			'options' => ['expires' => $expires] + $options + $this->defaults,
		];

		return $this;
	}

	/**
	 * Adds a raw unsigned cookie.
	 *
	 * @return $this
	 */
	public function addRaw(string $name, string $value, int $ttl = 0, array $options = [], ?string $group = null): Cookies
	{
		return $this->add($name, $value, $ttl, $options, true, $group);
	}

	/**
	 * Adds a signed cookie.
	 *
	 * @return $this
	 */
	public function addSigned(string $name, string $value, int $ttl = 0, array $options = [], bool $raw = false, ?string $group = null): Cookies
	{
		if (empty($this->signer)) {
			throw new HttpException('A [ Signer ] instance is required to sign cookies.');
		}

		return $this->add($name, $this->signer->sign($value), $ttl, $options, $raw, $group);
	}

	/**
	 * Adds a raw signed cookie.
	 *
	 * @return $this
	 */
	public function addRawSigned(string $name, string $value, int $ttl, array $options = [], ?string $group = null): Cookies
	{
		return $this->addSigned($name, $value, $ttl, $options, true, $group);
	}

	/**
	 * Returns TRUE if the cookie exists and FALSE if not.
	 */
	public function has(string $name): bool
	{
		return isset($this->cookies[$name]);
	}

	/**
	 * Removes a cookie.
	 *
	 * @return $this
	 */
	public function remove(string $name): Cookies
	{
		unset($this->cookies[$name]);

		return $this;
	}

	/**
	 * Deletes a cookie.
	 *
	 * @return $this
	 */
	public function delete(string $name, array $options = []): Cookies
	{
		return $this->add($name, '', -3600, $options);
	}

	/**
	 * Clears all the cookies.
	 *
	 * @return $this
	 */
	public function clear(): Cookies
	{
		$this->cookies = [];

		return $this;
	}

	/**
	 * Clears all the cookies except those that patch the provided names or patterns.
	 *
	 * @return $this
	 */
	public function clearExcept(array $cookies): Cookies
	{
		$this->cookies = array_filter($this->cookies, fn ($key) => $this->matchesPatterns($key, $cookies), ARRAY_FILTER_USE_KEY);

		return $this;
	}

	/**
	 * Removes all the cookies that aren't matched by the provided callback.
	 */
	public function filter(callable $callback): Cookies
	{
		$this->cookies = array_filter($this->cookies, $callback);

		return $this;
	}

	/**
	 * Returns all the cookies.
	 */
	public function all(): array
	{
		return $this->cookies;
	}
}
