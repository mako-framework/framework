<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use RuntimeException;

use function apcu_add;
use function apcu_clear_cache;
use function apcu_dec;
use function apcu_delete;
use function apcu_entry;
use function apcu_exists;
use function apcu_fetch;
use function apcu_inc;
use function apcu_store;
use function function_exists;

/**
 * APCU store.
 *
 * @author Frederic G. Østby
 */
class APCU extends Store implements IncrementDecrementInterface
{
	/**
	 * Whether to use atomic updates for getOrElse.
	 *
	 * This is a workaround for a known issue in ext-apcu, which breaks the apcu_entry
	 * function. See issue #244.
	 *
	 * @var bool
	 */
	protected $atomicGetOrElse = true;

	/**
	 * Constructor.
	 *
	 * @throws \RuntimeException
	 */
	public function __construct()
	{
		if(function_exists('apcu_store') === false)
		{
			throw new RuntimeException('APCU is not available on your system.');
		}
	}

	/**
	 * Set whether to use atomic get/set for getOrElse.
	 *
	 * @param  bool                    $toUse the new state.
	 * @return \mako\cache\stores\APCU
	 */
	public function useAtomicGetOrElse(bool $toUse): APCU
	{
		$this->atomicGetOrElse = $toUse;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function put(string $key, $data, int $ttl = 0): bool
	{
		return apcu_store($this->getPrefixedKey($key), $data, $ttl);
	}

	/**
	 * {@inheritdoc}
	 */
	public function putIfNotExists(string $key, $data, int $ttl = 0): bool
	{
		return apcu_add($this->getPrefixedKey($key), $data, $ttl);
	}

	/**
	 * {@inheritdoc}
	 */
	public function increment(string $key, int $step = 1)
	{
		return apcu_inc($this->getPrefixedKey($key), $step);
	}

	/**
	 * {@inheritdoc}
	 */
	public function decrement(string $key, int $step = 1)
	{
		return apcu_dec($this->getPrefixedKey($key), $step);
	}

	/**
	 * {@inheritdoc}
	 */
	public function has(string $key): bool
	{
		return apcu_exists($this->getPrefixedKey($key));
	}

	/**
	 * {@inheritdoc}
	 */
	public function get(string $key)
	{
		return apcu_fetch($this->getPrefixedKey($key));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOrElse(string $key, callable $data, int $ttl = 0)
	{
		if($this->atomicGetOrElse)
		{
			return apcu_entry($this->getPrefixedKey($key), $data, $ttl);
		}

		return parent::getOrElse($key, $data, $ttl);
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove(string $key): bool
	{
		return apcu_delete($this->getPrefixedKey($key));
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear(): bool
	{
		return apcu_clear_cache();
	}
}
