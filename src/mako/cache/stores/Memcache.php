<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use Memcache as PHPMemcache;

use mako\cache\stores\StoreInterface;
use mako\cache\stores\traits\GetOrElseTrait;

/**
 * Memcache store.
 *
 * @author  Frederic G. Østby
 */
class Memcache implements StoreInterface
{
	use GetOrElseTrait;

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
	 * @access  public
	 * @param   array    $servers       Memcache servers
	 * @param   int      $timeout       Timeout in seconds
	 * @param   boolean  $compressData  Compress data?
	 */
	public function __construct(array $servers, $timeout = 1, $compressData = false)
	{
		$this->memcache = new PHPMemcache();

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
	 * {@inheritdoc}
	 */
	public function put($key, $data, $ttl = 0)
	{
		if($ttl !== 0)
		{
			$ttl += time();
		}

		if($this->memcache->replace($key, $data, $this->compressionLevel, $ttl) === false)
		{
			return $this->memcache->set($key, $data, $this->compressionLevel, $ttl);
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has($key)
	{
		return ($this->memcache->get($key) !== false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($key)
	{
		return $this->memcache->get($key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove($key)
	{
		return $this->memcache->delete($key, 0);
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear()
	{
		return $this->memcache->flush();
	}
}