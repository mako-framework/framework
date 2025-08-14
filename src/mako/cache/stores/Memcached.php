<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use Memcached as PHPMemcached;
use Override;

use function time;

/**
 * Memcached store.
 */
class Memcached extends Store implements IncrementDecrementInterface
{
	/**
	 * Memcached instance.
	 */
	protected PHPMemcached $memcached;

	/**
	 * Constructor.
	 */
	public function __construct(array $servers, int $timeout = 1, bool $compressData = false)
	{
		$this->memcached = new PHPMemcached;

		$this->memcached->setOption(PHPMemcached::OPT_BINARY_PROTOCOL, true);

		if ($timeout !== 1) {
			$this->memcached->setOption(PHPMemcached::OPT_CONNECT_TIMEOUT, ($timeout * 1000)); // Multiply by 1000 to convert to ms
		}

		if ($compressData === false) {
			$this->memcached->setOption(PHPMemcached::OPT_COMPRESSION, false);
		}

		// Add servers to the connection pool

		foreach ($servers as $server) {
			$this->memcached->addServer($server['server'], $server['port'], $server['weight']);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function put(string $key, mixed $data, int $ttl = 0): bool
	{
		if ($ttl !== 0) {
			$ttl += time();
		}

		$key = $this->getPrefixedKey($key);

		if ($this->memcached->replace($key, $data, $ttl) === false) {
			return $this->memcached->set($key, $data, $ttl);
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function putIfNotExists(string $key, mixed $data, int $ttl = 0): bool
	{
		if ($ttl !== 0) {
			$ttl += time();
		}

		return $this->memcached->add($this->getPrefixedKey($key), $data, $ttl);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function increment(string $key, int $step = 1): false|int
	{
		return $this->memcached->increment($this->getPrefixedKey($key), $step, $step);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function decrement(string $key, int $step = 1): false|int
	{
		return $this->memcached->decrement($this->getPrefixedKey($key), $step, $step);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function has(string $key): bool
	{
		return $this->memcached->get($this->getPrefixedKey($key)) !== false;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function get(string $key): mixed
	{
		$value = $this->memcached->get($this->getPrefixedKey($key));

		if ($this->memcached->getResultCode() === PHPMemcached::RES_NOTFOUND) {
			return null;
		}

		return $value;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function remove(string $key): bool
	{
		return $this->memcached->delete($this->getPrefixedKey($key), 0);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function clear(): bool
	{
		return $this->memcached->flush();
	}
}
