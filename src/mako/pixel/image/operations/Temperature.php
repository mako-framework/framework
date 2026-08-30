<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use function max;
use function min;

/**
 * Adjusts the image color temperature.
 *
 * The level ranges from -100 (coolest) to 100 (warmest).
 * Values outside this range will be clamped.
 */
abstract class Temperature implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected int $level
	) {
		$this->level = max(-100, min(100, $level));
	}
}
