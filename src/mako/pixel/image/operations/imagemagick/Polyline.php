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
use mako\pixel\image\operations\Point;
use mako\pixel\image\operations\Points;
use Override;

use function count;

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
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$draw = new ImagickDraw;

		try {
			$draw->setFillColor('transparent');

			$draw->setStrokeColor(new ImagickPixel($this->stroke->toHexaString()));

			$draw->setStrokeWidth($this->strokeWidth);

			$points = [];

			foreach ($this->points as $point) {
				$points[] = [
					'x' => $point->x + $this->position->x,
					'y' => $point->y + $this->position->y,
				];
			}

			$draw->polyline($points);

			$imageResource->drawImage($draw);
		}
		finally {
			$draw->clear();
			$draw->destroy();
		}
	}
}
