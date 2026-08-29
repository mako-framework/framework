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

use function floor;
use function min;

/**
 * Draws a rounded rectangle on the image.
 */
abstract class RoundedRectangle implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected Dimensions $dimensions,
		protected int $radius,
		protected ?Color $fill = null,
		protected ?Color $stroke = null,
		protected int $strokeWidth = 1,
		protected Point $position = new Point(0, 0)
	) {
		if ($this->fill === null && $this->stroke === null) {
			throw new InvalidArgumentException('A rounded rectangle requires a fill, a stroke, or both.');
		}

		if ($this->stroke !== null && $this->strokeWidth < 1) {
			throw new InvalidArgumentException('Stroke width must be greater than 0.');
		}

		// Clamp the radius so that it never exceeds half the width or height,
		// preventing corner arcs from overlapping and producing a malformed polygon.

		$this->radius = min(
			$this->radius,
			(int) floor($this->dimensions->width / 2),
			(int) floor($this->dimensions->height / 2),
		);
	}
}
