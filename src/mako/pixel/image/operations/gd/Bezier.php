<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\Bezier as BezierOperation;
use mako\pixel\image\traits\GdTrait;
use Override;

use function count;
use function imagealphablending;
use function imageline;
use function imagesetthickness;

/**
 * {@inheritDoc}
 */
class Bezier extends BezierOperation
{
	use GdTrait;

	/**
	 * Number of line segments used to approximate the curve.
	 */
	protected const int SEGMENTS = 100;

	/**
	 * Calculates a point on the Bézier curve at position t using De Casteljau's algorithm.
	 */
	protected function calculatePoint(array $points, float $t): array
	{
		while (($count = count($points)) > 1) {
			$next = [];

			for ($i = 0, $last = $count - 1; $i < $last; ++$i) {
				$next[] = [
					'x' => $points[$i]['x'] + ($points[$i + 1]['x'] - $points[$i]['x']) * $t,
					'y' => $points[$i]['y'] + ($points[$i + 1]['y'] - $points[$i]['y']) * $t,
				];
			}

			$points = $next;
		}

		return $points[0];
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

		$points = [];

		foreach ($this->points as $point) {
			$points[] = [
				'x' => $point->x + $this->position->x,
				'y' => $point->y + $this->position->y,
			];
		}

		$previous = $this->calculatePoint($points, 0);

		for ($i = 1; $i <= static::SEGMENTS; $i++) {
			$t = $i / static::SEGMENTS;

			$current = $this->calculatePoint($points, $t);

			imageline(
				$imageResource,
				(int) $previous['x'],
				(int) $previous['y'],
				(int) $current['x'],
				(int) $current['y'],
				$color
			);

			$previous = $current;
		}

		imagesetthickness($imageResource, 1);
	}
}
