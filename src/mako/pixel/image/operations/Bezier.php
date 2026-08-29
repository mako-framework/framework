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
 * Draws a Bézier curve on the image.
 */
abstract class Bezier implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected Points $points,
		protected Color $stroke,
		protected int $strokeWidth = 1,
		protected Point $position = new Point(0, 0)
	) {
		if (count($points) < 2) {
			throw new InvalidArgumentException('A Bézier curve requires at least 2 points.');
		}

		if ($this->strokeWidth < 1) {
			throw new InvalidArgumentException('Stroke width must be greater than 0.');
		}
	}
}
