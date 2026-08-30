<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use function max;
use function min;

/**
 * Adjusts the opacity of the image.
 *
 * The opacity ranges from 0 (fully transparent) to 100 (fully opaque).
 * Values outside this range will be clamped.
 */
abstract class Opacity implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected int $opacity
	) {
		$this->opacity = max(0, min(100, $opacity));
	}
}
