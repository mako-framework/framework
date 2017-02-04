<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use Memcached as PHPMemcached;

use mako\cache\stores\Store;

/**
 * Memcached store.
 *
 * @author Frederic G. Østby
 */
class Memcached extends Store
{
	/**
	 * Memcached instance.
	 *
	 * @var \Memcached
	 */
	protected $memcached;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @param array $servers      Memcache servers
	 * @param int   $timeout      Timeout in seconds
	 * @param bool  $compressData Compress data?
	 */
	public function __construct(array $servers, int $timeout = 1, bool $compressData = false)
	{
		$this->memcached = new PHPMemcached();

		if($timeout !== 1)
		{
			$this->memcached->setOption(PHPMemcached::OPT_CONNECT_TIMEOUT, ($timeout * 1000)); // Multiply by 1000 to convert to ms
		}

		if($compressData === false)
		{
			$this->memcached->setOption(PHPMemcached::OPT_COMPRESSION, false);
		}

		// Add servers to the connection pool

		foreach($servers as $server)
		{
			$this->memcached->addServer($server['server'], $server['port'], $server['weight']);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function put(string $key, $data, int $ttl = 0): bool
	{
		if($ttl !== 0)
		{
			$ttl += time();
		}

		$key = $this->getPrefixedKey($key);

		if($this->memcached->replace($key, $data, $ttl) === false)
		{
			return $this->memcached->set($key, $data, $ttl);
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has(string $key): bool
	{
		return ($this->memcached->get($this->getPrefixedKey($key)) !== false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get(string $key)
	{
		return $this->memcached->get($this->getPrefixedKey($key));
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove(string $key): bool
	{
		return $this->memcached->delete($this->getPrefixedKey($key), 0);
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear(): bool
	{
		return $this->memcached->flush();
	}
}
