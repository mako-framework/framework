<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use RuntimeException;

/**
 * Zend disk store.
 *
 * @author Frederic G. Østby
 */
class ZendDisk extends Store
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		if(function_exists('zend_disk_cache_store') === false)
		{
			throw new RuntimeException('Zend disk cache is not available on your system.');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function put(string $key, $data, int $ttl = 0): bool
	{
		return zend_disk_cache_store($this->getPrefixedKey($key), $data, $ttl);
	}

	/**
	 * {@inheritdoc}
	 */
	public function has(string $key): bool
	{
		return (zend_disk_cache_fetch($this->getPrefixedKey($key)) !== false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get(string $key)
	{
		return zend_disk_cache_fetch($this->getPrefixedKey($key));
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove(string $key): bool
	{
		return zend_disk_cache_delete($this->getPrefixedKey($key));
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear(): bool
	{
		return zend_disk_cache_clear();
	}
}
