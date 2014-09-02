<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use \mako\cache\stores\StoreInterface;

/**
 * Null store.
 *
 * @author  Frederic G. Østby
 */

class Null implements StoreInterface
{
	/**
	 * {@inheritdoc}
	 */

	public function put($key, $data, $ttl = 0)
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */

	public function has($key)
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */

	public function get($key)
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */

	public function remove($key)
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */

	public function clear()
	{
		return true;
	}
}