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
use mako\pixel\image\operations\Vertices;
use Override;

use function count;
use function imagealphablending;
use function imagecolorallocatealpha;
use function imagefilledpolygon;
use function imagepolygon;
use function imagesetthickness;
use function round;

/**
 * Draws a polygon.
 */
class Polygon implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Vertices $vertices,
		protected ?Color $fill = null,
		protected ?Color $stroke = null,
		protected int $strokeWidth = 1,
		protected int $offsetX = 0,
		protected int $offsetY = 0
	) {
		if (count($vertices) < 3) {
			throw new InvalidArgumentException('A polygon requires at least 3 vertices.');
		}

		if ($this->fill === null && $this->stroke === null) {
			throw new InvalidArgumentException('A polygon requires either a fill or a stroke.');
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

		foreach ($this->vertices as $point) {
			$points[] = $point->x + $this->offsetX;
			$points[] = $point->y + $this->offsetY;
		}

		imagealphablending($imageResource, true);

		if ($this->fill !== null) {
			imagefilledpolygon(
				$imageResource,
				$points,
				imagecolorallocatealpha(
					$imageResource,
					$this->fill->red,
					$this->fill->green,
					$this->fill->blue,
					127 - (int) round($this->fill->alpha / 255 * 127),
				),
			);
		}

		if ($this->stroke !== null) {
			imagesetthickness($imageResource, $this->strokeWidth);

			imagepolygon(
				$imageResource,
				$points,
				imagecolorallocatealpha(
					$imageResource,
					$this->stroke->red,
					$this->stroke->green,
					$this->stroke->blue,
					127 - (int) round($this->stroke->alpha / 255 * 127),
				),
			);

			imagesetthickness($imageResource, 1);
		}
	}
}
