<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use Memcache as PHPMemcache;
use Override;

use function time;

/**
 * Memcache store.
 */
class Memcache extends Store
{
	/**
	 * Memcache instance.
	 */
	protected PHPMemcache $memcache;

	/**
	 * Compression level.
	 */
	protected int $compressionLevel = 0;

	/**
	 * Constructor.
	 */
	public function __construct(array $servers, int $timeout = 1, bool $compressData = false)
	{
		$this->memcache = new PHPMemcache;

		if ($compressData === true) {
			$this->compressionLevel = MEMCACHE_COMPRESSED;
		}

		// Add servers to the connection pool

		foreach ($servers as $server) {
			$this->memcache->addServer($server['server'], $server['port'], $server['persistent_connection'], $server['weight'], $timeout);
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

		if ($this->memcache->replace($key, $data, $this->compressionLevel, $ttl) === false) {
			return $this->memcache->set($key, $data, $this->compressionLevel, $ttl);
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

		return $this->memcache->add($this->getPrefixedKey($key), $data, $this->compressionLevel, $ttl);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function has(string $key): bool
	{
		return $this->memcache->get($this->getPrefixedKey($key)) !== false;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function get(string $key): mixed
	{
		$success = false;

		$value = $this->memcache->get($this->getPrefixedKey($key), $success);

		return $success !== false ? $value : null;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function remove(string $key): bool
	{
		return $this->memcache->delete($this->getPrefixedKey($key), 0);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function clear(): bool
	{
		return $this->memcache->flush();
	}
}
