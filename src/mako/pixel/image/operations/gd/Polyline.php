<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use InvalidArgumentException;
use mako\pixel\image\Color;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\Point;
use mako\pixel\image\operations\Points;
use Override;

use function count;
use function imagealphablending;
use function imagecolorallocatealpha;
use function imageline;
use function imagesetthickness;
use function round;

/**
 * Draws a polyline on the image.
 */
class Polyline implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Points $points,
		protected Color $stroke,
		protected int $strokeWidth = 1,
		protected Point $offset = new Point(0, 0)
	) {
		if (count($points) < 2) {
			throw new InvalidArgumentException('A polyline requires at least 2 points.');
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
		imagealphablending($imageResource, true);

		imagesetthickness($imageResource, $this->strokeWidth);

		$color = imagecolorallocatealpha(
			$imageResource,
			$this->stroke->red,
			$this->stroke->green,
			$this->stroke->blue,
			127 - (int) round($this->stroke->alpha / 255 * 127),
		);

		$points = $this->points->getPoints();

		foreach ($points as $key => $point) {
			if (!isset($points[$key + 1])) {
				break;
			}

			$next = $points[$key + 1];

			imageline(
				$imageResource,
				$point->x + $this->offset->x,
				$point->y +  $this->offset->y,
				$next->x +  $this->offset->x,
				$next->y +  $this->offset->y,
				$color
			);
		}

		imagesetthickness($imageResource, 1);
	}
}
