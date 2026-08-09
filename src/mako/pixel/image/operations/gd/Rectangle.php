<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use InvalidArgumentException;
use mako\pixel\image\Color;
use mako\pixel\image\Dimensions;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\Point;
use mako\pixel\image\traits\GdTrait;
use Override;

use function imagealphablending;
use function imagefilledrectangle;
use function imagerectangle;
use function imagesetthickness;

/**
 * Draws a rectangle on the image.
 */
class Rectangle implements OperationInterface
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
		protected Point $position = new Point(0, 0)
	) {
		if ($this->fill === null && $this->stroke === null) {
			throw new InvalidArgumentException('A rectangle requires a fill, a stroke, or both.');
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
			imagefilledrectangle(
				$imageResource,
				$this->position->x,
				$this->position->y,
				$this->position->x + $this->dimensions->width,
				$this->position->y + $this->dimensions->height,
				$this->allocateColor($imageResource, $this->fill)
			);
		}

		if ($this->stroke !== null) {
			imagesetthickness($imageResource, $this->strokeWidth);

			imagerectangle(
				$imageResource,
				$this->position->x,
				$this->position->y,
				$this->position->x + $this->dimensions->width,
				$this->position->y + $this->dimensions->height,
				$this->allocateColor($imageResource, $this->stroke)
			);

			imagesetthickness($imageResource, 1);
		}
	}
}
