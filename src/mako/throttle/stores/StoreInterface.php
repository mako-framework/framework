<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\throttle\stores;

use DateTimeInterface;

/**
 * Store interface.
 */
interface StoreInterface
{
	/**
	 * Returns the number of hits for the key.
	 */
	public function getHits(string $key): int;

	/**
	 * Returns the expiration date and time for the key or NULL if the key doesn't exist.
	 */
	public function getExpiration(string $key): ?DateTimeInterface;

	/**
	 * Increments the number of hits for the key and returns the new value.
	 */
	public function increment(string $key, DateTimeInterface $expiresAt): int;
}
