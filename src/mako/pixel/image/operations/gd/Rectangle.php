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
use Override;

use function imagealphablending;
use function imagecolorallocatealpha;
use function imagefilledrectangle;
use function imagerectangle;
use function imagesetthickness;
use function round;

/**
 * Draws a rectangle on the image.
 */
class Rectangle implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Dimensions $dimensions,
		protected ?Color $fill = null,
		protected ?Color $stroke = null,
		protected int $strokeWidth = 1,
		protected Point $offset = new Point(0, 0)
	) {
		if ($this->fill === null && $this->stroke === null) {
			throw new InvalidArgumentException('A rectangle requires either a fill or a stroke.');
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
				$this->offset->x,
				$this->offset->y,
				$this->offset->x + $this->dimensions->width,
				$this->offset->y + $this->dimensions->height,
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

			imagerectangle(
				$imageResource,
				$this->offset->x,
				$this->offset->y,
				$this->offset->x + $this->dimensions->width,
				$this->offset->y + $this->dimensions->height,
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
