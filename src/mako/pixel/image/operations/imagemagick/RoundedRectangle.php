<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use ImagickDraw;
use ImagickPixel;
use InvalidArgumentException;
use mako\pixel\image\Color;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\operations\OperationInterface;
use Override;

/**
 * Draws a rounded rectangle on the image.
 */
class RoundedRectangle implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Dimensions $dimensions,
		protected int $radius,
		protected ?Color $fill = null,
		protected ?Color $stroke = null,
		protected int $strokeWidth = 1,
		protected Point $position = new Point(0, 0)
	) {
		if ($this->fill === null && $this->stroke === null) {
			throw new InvalidArgumentException('A rounded rectangle requires a fill, a stroke, or both.');
		}

		if ($this->stroke !== null && $this->strokeWidth < 1) {
			throw new InvalidArgumentException('Stroke width must be greater than 0.');
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$draw = new ImagickDraw;

		try {
			$draw->setFillColor(new ImagickPixel($this->fill?->toHexaString() ?? 'transparent'));

			if ($this->stroke !== null) {
				$draw->setStrokeColor(new ImagickPixel($this->stroke->toHexaString()));

				$draw->setStrokeWidth($this->strokeWidth);
			}

			$draw->roundRectangle(
				$this->position->x,
				$this->position->y,
				$this->position->x + $this->dimensions->width,
				$this->position->y + $this->dimensions->height,
				$this->radius,
				$this->radius
			);

			$imageResource->drawImage($draw);
		}
		finally {
			$draw->clear();
			$draw->destroy();
		}
	}
}
