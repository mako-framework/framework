<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use mako\pixel\image\operations\traits\NormalizeTrait;

/**
 * Adjusts the image color temperature.
 *
 * The level ranges from -100 (coolest) to 100 (warmest).
 * Values outside this range will be clamped.
 */
abstract class Temperature implements OperationInterface
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
