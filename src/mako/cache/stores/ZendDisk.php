<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use RuntimeException;

use function function_exists;
use function zend_disk_cache_clear;
use function zend_disk_cache_delete;
use function zend_disk_cache_fetch;
use function zend_disk_cache_store;

/**
 * Zend disk store.
 *
 * @deprecated 7.0
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
	 * {@inheritDoc}
	 */
	public function put(string $key, $data, int $ttl = 0): bool
	{
		return zend_disk_cache_store($this->getPrefixedKey($key), $data, $ttl);
	}

	/**
	 * {@inheritDoc}
	 */
	public function has(string $key): bool
	{
		return (zend_disk_cache_fetch($this->getPrefixedKey($key)) !== false);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get(string $key)
	{
		return zend_disk_cache_fetch($this->getPrefixedKey($key));
	}

	/**
	 * {@inheritDoc}
	 */
	public function remove(string $key): bool
	{
		return zend_disk_cache_delete($this->getPrefixedKey($key));
	}

	/**
	 * {@inheritDoc}
	 */
	public function clear(): bool
	{
		return zend_disk_cache_clear();
	}
}
