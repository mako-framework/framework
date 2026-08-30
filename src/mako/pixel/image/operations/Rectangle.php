<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use InvalidArgumentException;
use mako\pixel\image\Color;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\geometry\Point;

/**
 * Draws a rectangle on the image.
 */
abstract class Rectangle implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected Dimensions $dimensions,
		protected ?Color $fill = null,
		protected ?Color $stroke = null,
		protected int $strokeWidth = 1,
		protected Point $position = new Point(0, 0)
	) {
		if ($fill === null && $stroke === null) {
			throw new InvalidArgumentException('A rectangle requires a fill, a stroke, or both.');
		}

		if ($stroke !== null && $strokeWidth < 1) {
			throw new InvalidArgumentException('Stroke width must be greater than 0.');
		}
	}
}
