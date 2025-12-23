<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\traits;

use function max;
use function min;

/**
 * Trait containing common value normalization methods.
 */
trait NormalizeTrait
{
	/**
	 * Makes sure that the level is between -100 and 100.
	 */
	protected function normalizeLevel(int $level): int
	{
		return max(-100, min(100, $level));
	}
}
