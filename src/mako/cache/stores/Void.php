<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use mako\cache\stores\StoreInterface;
use mako\cache\stores\traits\GetOrElseTrait;

/**
 * Void store.
 *
 * @author  Frederic G. Østby
 */
class Void implements StoreInterface
{
	use GetOrElseTrait;

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