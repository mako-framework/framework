<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use mako\pixel\image\Color;
use mako\pixel\image\geometry\Point;

/**
 * Draws a pixel on the image at the specified coordinates.
 */
abstract class Pixel implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected Point $pixel,
		protected Color $color
	) {
	}
}
