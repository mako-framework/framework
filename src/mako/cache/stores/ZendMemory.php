<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use RuntimeException;

use mako\cache\stores\StoreInterface;
use mako\cache\stores\traits\GetOrElseTrait;

/**
 * Zend memory store.
 *
 * @author  Frederic G. Østby
 */
class ZendMemory implements StoreInterface
{
	use GetOrElseTrait;

	/**
	 * Constructor.
	 *
	 * @access  public
	 */
	public function __construct()
	{
		if(function_exists('zend_shm_cache_store') === false)
		{
			throw new RuntimeException(vsprintf("%s(): Zend memory cache is not available on your system.", [__METHOD__]));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function put(string $key, $data, int $ttl = 0): bool
	{
		return zend_shm_cache_store($key, $data, $ttl);
	}

	/**
	 * {@inheritdoc}
	 */
	public function has(string $key): bool
	{
		return (zend_disk_cache_fetch($key) !== false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get(string $key)
	{
		return zend_shm_cache_fetch($key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove(string $key): bool
	{
		return zend_shm_cache_delete($key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear(): bool
	{
		return zend_shm_cache_clear();
	}
}
