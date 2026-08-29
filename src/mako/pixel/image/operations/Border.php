<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use mako\pixel\image\Color;

use function max;

/**
 * Adds a border to the image.
 */
abstract class Border implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected Color $color = new Color(0, 0, 0),
		protected int $width = 4
	) {
		$this->width = max(0, $this->width);
	}
}
