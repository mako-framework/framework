<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use function max;
use function min;

/**
 * Adjusts the image brightness.
 *
 * The level ranges from -100 (minimum brightness) to 100 (maximum brightness).
 * Values outside this range will be clamped.
 */
abstract class Brightness implements OperationInterface
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
