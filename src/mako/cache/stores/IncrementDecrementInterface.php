<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

/**
 * Increment and decrement interface.
 */
interface IncrementDecrementInterface
{
	/**
	 * Increments a stored number.
	 */
	public function increment(string $key, int $step = 1): false|int;

	/**
	 * Decrements a stored number.
	 */
	public function decrement(string $key, int $step = 1): false|int;
}
