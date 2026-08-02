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
use Override;

use function imagealphablending;
use function imagecolorallocatealpha;
use function imageellipse;
use function imagefilledellipse;
use function imagesetthickness;
use function round;

/**
 * Draws a circle on the image.
 */
class Circle implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $radius,
		protected ?Color $fill = null,
		protected ?Color $stroke = null,
		protected int $strokeWidth = 1,
		protected Point $center = new Point(0, 0)
	) {
		if ($this->fill === null && $this->stroke === null) {
			throw new InvalidArgumentException('A circle requires a fill, a stroke, or both.');
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

		$diameter = $this->radius * 2;

		if ($this->fill !== null) {
			imagefilledellipse(
				$imageResource,
				$this->center->x,
				$this->center->y,
				$diameter,
				$diameter,
				imagecolorallocatealpha(
					$imageResource,
					$this->fill->red,
					$this->fill->green,
					$this->fill->blue,
					127 - (int) round($this->fill->alpha / 255 * 127),
				)
			);
		}

		if ($this->stroke !== null) {
			imagesetthickness($imageResource, $this->strokeWidth);

			imageellipse(
				$imageResource,
				$this->center->x,
				$this->center->y,
				$diameter,
				$diameter,
				imagecolorallocatealpha(
					$imageResource,
					$this->stroke->red,
					$this->stroke->green,
					$this->stroke->blue,
					127 - (int) round($this->stroke->alpha / 255 * 127),
				)
			);

			imagesetthickness($imageResource, 1);
		}
	}
}
