<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\throttle\stores;

use DateTime;
use DateTimeInterface;

use function apcu_add;
use function apcu_fetch;
use function apcu_inc;
use function apcu_key_info;
use function hash;
use function time;

/**
 * APCu store.
 */
class APCu implements StoreInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $prefix = 'throttle:'
	) {
	}

	/**
	 * Returns the rate limit key.
	 */
	protected function getKey(string $key): string
	{
		return $this->prefix . hash('xxh128', $key);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHits(string $key): int
	{
		return apcu_fetch($this->getKey($key)) ?: 0;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getExpiration(string $key): ?DateTimeInterface
	{
		$info = apcu_key_info($this->getKey($key));

		if ($info === null) {
			return null;
		}

		return DateTime::createFromTimestamp($info['creation_time'] + $info['ttl']);
	}

	/**
	 * {@inheritDoc}
	 */
	public function increment(string $key, DateTimeInterface $expiresAt): int
	{
		$key = $this->getKey($key);

		apcu_add($key, 0, $expiresAt->getTimestamp() - time());

		return apcu_inc($key);
	}
}
