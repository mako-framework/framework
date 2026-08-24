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
use function imageline;
use function imagesetthickness;

/**
 * Draws a polyline on the image.
 */
class Polyline implements OperationInterface
{
	use GdTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Points $points,
		protected Color $stroke,
		protected int $strokeWidth = 1,
		protected Point $position = new Point(0, 0)
	) {
		if (count($points) < 2) {
			throw new InvalidArgumentException('A polyline requires at least 2 points.');
		}

		if ($this->strokeWidth < 1) {
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

		$color = $this->allocateColor($imageResource, $this->stroke);

		$points = $this->points->getPoints();

		foreach ($points as $key => $point) {
			if (!isset($points[$key + 1])) {
				break;
			}

			$next = $points[$key + 1];

			imageline(
				$imageResource,
				$point->x + $this->position->x,
				$point->y +  $this->position->y,
				$next->x +  $this->position->x,
				$next->y +  $this->position->y,
				$color
			);
		}

		imagesetthickness($imageResource, 1);
	}
}
