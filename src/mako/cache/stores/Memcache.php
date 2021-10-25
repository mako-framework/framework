<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use Memcache as PHPMemcache;

use function time;

/**
 * Memcache store.
 *
 * @author Frederic G. Østby
 */
class Memcache extends Store
{
	/**
	 * Memcache instance.
	 *
	 * @var \Memcache
	 */
	protected $memcache;

	/**
	 * Compression level.
	 *
	 * @var int
	 */
	protected $compressionLevel = 0;

	/**
	 * Constructor.
	 *
	 * @param array $servers      Memcache servers
	 * @param int   $timeout      Timeout in seconds
	 * @param bool  $compressData Compress data?
	 */
	public function __construct(array $servers, int $timeout = 1, bool $compressData = false)
	{
		$this->memcache = new PHPMemcache;

		if($compressData === true)
		{
			$this->compressionLevel = MEMCACHE_COMPRESSED;
		}

		// Add servers to the connection pool

		foreach($servers as $server)
		{
			$this->memcache->addServer($server['server'], $server['port'], $server['persistent_connection'], $server['weight'], $timeout);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function put(string $key, $data, int $ttl = 0): bool
	{
		if($ttl !== 0)
		{
			$ttl += time();
		}

		$key = $this->getPrefixedKey($key);

		if($this->memcache->replace($key, $data, $this->compressionLevel, $ttl) === false)
		{
			return $this->memcache->set($key, $data, $this->compressionLevel, $ttl);
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function putIfNotExists(string $key, $data, int $ttl = 0): bool
	{
		if($ttl !== 0)
		{
			$ttl += time();
		}

		return $this->memcache->add($this->getPrefixedKey($key), $data, $this->compressionLevel, $ttl);
	}

	/**
	 * {@inheritDoc}
	 */
	public function has(string $key): bool
	{
		return ($this->memcache->get($this->getPrefixedKey($key)) !== false);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get(string $key)
	{
		$success = false;

		$value = $this->memcache->get($this->getPrefixedKey($key), $success);

		return $success !== false ? $value : null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function remove(string $key): bool
	{
		return $this->memcache->delete($this->getPrefixedKey($key), 0);
	}

	/**
	 * {@inheritDoc}
	 */
	public function clear(): bool
	{
		return $this->memcache->flush();
	}
}
