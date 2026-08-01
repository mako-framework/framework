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
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\Vertices;
use Override;

use function count;

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
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$draw = new ImagickDraw;

		try {
			$draw->setFillColor(new ImagickPixel($this->fill?->toRgbaString() ?? 'transparent'));

			if ($this->stroke !== null) {
				$draw->setStrokeColor(new ImagickPixel($this->stroke->toRgbaString()));

				$draw->setStrokeWidth($this->strokeWidth);
			}

			$points = [];

			foreach ($this->vertices as $point) {
				$points[] = [
					'x' => $point->x + $this->offsetX,
					'y' => $point->y + $this->offsetY,
				];
			}

			$draw->polygon($points);

			$imageResource->drawImage($draw);
		}
		finally {
			$draw->clear();
			$draw->destroy();
		}
	}
}
