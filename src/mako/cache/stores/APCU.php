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
	public function put($key, $data, $ttl = 0)
	{
		return apcu_store($key, $data, $ttl);
	}

	/**
	 * {@inheritdoc}
	 */
	public function has($key)
	{
		return apcu_exists($key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($key)
	{
		return apcu_fetch($key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOrElse($key, callable $data, $ttl = 0)
	{
		return apcu_entry($key, $data, $ttl);
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove($key)
	{
		return apcu_delete($key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear()
	{
		return apcu_clear_cache();
	}
}