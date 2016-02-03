<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use RuntimeException;

use mako\cache\stores\StoreInterface;

/**
 * WinCache store.
 *
 * @author  Frederic G. Østby
 */

class WinCache implements StoreInterface
{
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

	public function put($key, $data, $ttl = 0)
	{
		return wincache_ucache_set($key, $data, $ttl);
	}

	/**
	 * {@inheritdoc}
	 */

	public function has($key)
	{
		return wincache_ucache_exists($key);
	}

	/**
	 * {@inheritdoc}
	 */

	public function get($key)
	{
		$cache = wincache_ucache_get($key, $success);

		if($success === true)
		{
			return $cache;
		}
		else
		{
			return false;
		}
	}

	/**
	 * {@inheritdoc}
	 */

	public function remove($key)
	{
		return wincache_ucache_delete($key);
	}

	/**
	 * {@inheritdoc}
	 */

	public function clear()
	{
		return wincache_ucache_clear();
	}
}