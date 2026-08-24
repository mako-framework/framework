<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use InvalidArgumentException;
use mako\pixel\image\Color;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\traits\GdTrait;
use Override;

use function imagealphablending;
use function imageellipse;
use function imagefilledellipse;
use function imagesetthickness;

/**
 * Draws an ellipse on the image.
 */
class Ellipse implements OperationInterface
{
	use GdTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Dimensions $dimensions,
		protected ?Color $fill = null,
		protected ?Color $stroke = null,
		protected int $strokeWidth = 1,
		protected Point $center = new Point(0, 0)
	) {
		if ($this->fill === null && $this->stroke === null) {
			throw new InvalidArgumentException('An ellipse requires a fill, a stroke, or both.');
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

		if ($this->fill !== null) {
			imagefilledellipse(
				$imageResource,
				$this->center->x,
				$this->center->y,
				$this->dimensions->width,
				$this->dimensions->height,
				$this->allocateColor($imageResource, $this->fill)
			);
		}

		if ($this->stroke !== null) {
			imagesetthickness($imageResource, $this->strokeWidth);

			imageellipse(
				$imageResource,
				$this->center->x,
				$this->center->y,
				$this->dimensions->width,
				$this->dimensions->height,
				$this->allocateColor($imageResource, $this->stroke)
			);

			imagesetthickness($imageResource, 1);
		}
	}
}
