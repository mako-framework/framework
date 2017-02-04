<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use mako\cache\stores\Store;

/**
 * Null store.
 *
 * @author Frederic G. Østby
 */
class NullStore extends Store
{
	/**
	 * {@inheritdoc}
	 */
	public function put(string $key, $data, int $ttl = 0): bool
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has(string $key): bool
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get(string $key)
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove(string $key): bool
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear(): bool
	{
		return true;
	}
}
