<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use function max;
use function min;

/**
 * Adjusts the color saturation.
 *
 * The level ranges from -100 (minimum saturation) to 100 (maximum saturation).
 * Values outside this range will be clamped.
 */
abstract class Saturation implements OperationInterface
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
