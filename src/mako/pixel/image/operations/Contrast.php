<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use mako\pixel\image\operations\traits\NormalizeTrait;

/**
 * Adjusts the image contrast.
 *
 * The level ranges from -100 (minimum contrast) to 100 (maximum contrast).
 * Values outside this range will be clamped.
 */
abstract class Contrast implements OperationInterface
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
