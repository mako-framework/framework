<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use mako\pixel\image\operations\traits\NormalizeTrait;

/**
 * Adjusts the color saturation.
 *
 * The level ranges from -100 (minimum saturation) to 100 (maximum saturation).
 * Values outside this range will be clamped.
 */
abstract class Saturation implements OperationInterface
{
	use NormalizeTrait;

	/**
	 * Constructor.
	 */
	final public function __construct(
		protected int $level
	) {
		$this->level = $this->normalizeLevel($level);
	}
}
