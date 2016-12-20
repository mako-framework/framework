<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use mako\cache\stores\StoreInterface;
use mako\cache\stores\traits\GetOrElseTrait;

/**
 * Null store.
 *
 * @author Frederic G. Østby
 */
class NullStore implements StoreInterface
{
	use GetOrElseTrait;

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
