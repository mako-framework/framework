<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use Memcached as PHPMemcached;

use mako\cache\stores\StoreInterface;

/**
 * Memcached store.
 *
 * @author  Frederic G. Østby
 */
class Memcached implements StoreInterface
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
	 * @access  public
	 * @param   array    $servers       Memcache servers
	 * @param   int      $timeout       Timeout in seconds
	 * @param   boolean  $compressData  Compress data?
	 */
	public function __construct(array $servers, $timeout = 1, $compressData = false)
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
	public function put($key, $data, $ttl = 0)
	{
		if($ttl !== 0)
		{
			$ttl += time();
		}

		if($this->memcached->replace($key, $data, $ttl) === false)
		{
			return $this->memcached->set($key, $data, $ttl);
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has($key)
	{
		return ($this->memcached->get($key) !== false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($key)
	{
		return $this->memcached->get($key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove($key)
	{
		return $this->memcached->delete($key, 0);
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear()
	{
		return $this->memcached->flush();
	}
}