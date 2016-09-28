<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use RuntimeException;

use mako\cache\stores\StoreInterface;

/**
 * APCU store.
 *
 * @author  Frederic G. Østby
 */
class APCU implements StoreInterface
{
	/**
	 * Constructor.
	 *
	 * @access  public
	 */
	public function __construct()
	{
		if(function_exists('apcu_store') === false)
		{
			throw new RuntimeException(vsprintf("%s(): APCU is not available on your system.", [__METHOD__]));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function put(string $key, $data, int $ttl = 0): bool
	{
		return apcu_store($key, $data, $ttl);
	}

	/**
	 * {@inheritdoc}
	 */
	public function has(string $key): bool
	{
		return apcu_exists($key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get(string $key)
	{
		return apcu_fetch($key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOrElse(string $key, callable $data, int $ttl = 0)
	{
		return apcu_entry($key, $data, $ttl);
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove(string $key): bool
	{
		return apcu_delete($key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear(): bool
	{
		return apcu_clear_cache();
	}
}