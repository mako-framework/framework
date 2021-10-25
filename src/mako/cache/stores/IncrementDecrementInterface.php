<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

/**
 * Increment and decrement interface.
 *
 * @author Frederic G. Østby
 */
interface IncrementDecrementInterface
{
	/**
	 * Increments a stored number.
	 *
	 * @param  string   $key  Cache key
	 * @param  int      $step Step
	 * @return bool|int
	 */
	public function increment(string $key, int $step = 1);

	/**
	 * Decrements a stored number.
	 *
	 * @param  string   $key  Cache key
	 * @param  int      $step Step
	 * @return bool|int
	 */
	public function decrement(string $key, int $step = 1);
}
