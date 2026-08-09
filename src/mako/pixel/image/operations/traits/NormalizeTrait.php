<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\traits;

use function max;
use function min;

/**
 * Trait containing common methods methods for normalizing values.
 */
trait NormalizeTrait
{
	/**
	 * Makes sure that the percent value is between 0 and 100.
	 */
	protected function normalizePercent(int $percent): int
	{
		return max(0, min(100, $percent));
	}

	/**
	 * Makes sure that the level value is between -100 and 100.
	 */
	protected function normalizeLevel(int $level): int
	{
		return max(-100, min(100, $level));
	}
}
