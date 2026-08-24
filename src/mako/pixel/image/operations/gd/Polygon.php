<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use InvalidArgumentException;
use mako\pixel\image\Color;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\geometry\Points;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\traits\GdTrait;
use Override;

use function count;
use function imagealphablending;
use function imagefilledpolygon;
use function imagepolygon;
use function imagesetthickness;

/**
 * Draws a polygon on the image.
 */
class Polygon implements OperationInterface
{
	use GdTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
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

	/**
	 * {@inheritDoc}
	 *
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$points = [];

		foreach ($this->points as $point) {
			$points[] = $point->x + $this->position->x;
			$points[] = $point->y + $this->position->y;
		}

		imagealphablending($imageResource, true);

		if ($this->fill !== null) {
			imagefilledpolygon(
				$imageResource,
				$points,
				$this->allocateColor($imageResource, $this->fill),
			);
		}

		if ($this->stroke !== null) {
			imagesetthickness($imageResource, $this->strokeWidth);

			imagepolygon(
				$imageResource,
				$points,
				$this->allocateColor($imageResource, $this->stroke),
			);

			imagesetthickness($imageResource, 1);
		}
	}
}
