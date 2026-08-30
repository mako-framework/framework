<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use mako\pixel\image\operations\traits\NormalizeTrait;

/**
 * Adjusts the opacity of the image.
 *
 * The opacity ranges from 0 (fully transparent) to 100 (fully opaque).
 * Values outside this range will be clamped.
 */
abstract class Opacity implements OperationInterface
{
	use NormalizeTrait;

	/**
	 * Constructor.
	 */
	final public function __construct(
		protected int $opacity
	) {
		$this->opacity = $this->normalizePercent($opacity);
	}
}
