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
 * WinCache store.
 *
 * @author  Frederic G. Østby
 */
class WinCache implements StoreInterface
{
	use GetOrElseTrait;

	/**
	 * Constructor.
	 *
	 * @access  public
	 */
	public function __construct()
	{
		if(function_exists('wincache_ucache_set') === false)
		{
			throw new RuntimeException(vsprintf("%s(): WinCache is not available on your system.", [__METHOD__]));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function put(string $key, $data, int $ttl = 0): bool
	{
		return wincache_ucache_set($key, $data, $ttl);
	}

	/**
	 * {@inheritdoc}
	 */
	public function has(string $key): bool
	{
		return wincache_ucache_exists($key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get(string $key)
	{
		$cache = wincache_ucache_get($key, $success);

		if($success === true)
		{
			return $cache;
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove(string $key): bool
	{
		return wincache_ucache_delete($key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear(): bool
	{
		return wincache_ucache_clear();
	}
}