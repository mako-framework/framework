<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use InvalidArgumentException;
use mako\pixel\image\Color;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\geometry\Points;

use function count;

/**
 * Draws a polygon on the image.
 */
abstract class Polygon implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected Points $points,
		protected ?Color $fill = null,
		protected ?Color $stroke = null,
		protected int $strokeWidth = 1,
		protected Point $position = new Point(0, 0)
	) {
		if (count($points) < 3) {
			throw new InvalidArgumentException('A polygon requires at least 3 points.');
		}

		if ($this->fill === null && $this->stroke === null) {
			throw new InvalidArgumentException('A polygon requires a fill, a stroke, or both.');
		}

		if ($this->stroke !== null && $this->strokeWidth < 1) {
			throw new InvalidArgumentException('Stroke width must be greater than 0.');
		}
	}
}
