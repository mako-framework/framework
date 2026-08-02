<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\inspectors\gd;

use GdImage;
use mako\pixel\exceptions\PixelException;
use mako\pixel\image\Color;
use mako\pixel\image\inspectors\InspectorInterface;
use mako\pixel\image\operations\Point;
use Override;

use function imagecolorat;
use function imagesx;
use function imagesy;
use function max;
use function min;
use function round;
use function sprintf;

/**
 * Returns the color of the specified pixel.
 *
 * @implements InspectorInterface<Color>
 */
class PixelColor implements InspectorInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Point $pixel
	) {
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function inspect(object &$imageResource): mixed
	{
		if (
			$this->pixel->x < 0 ||
			$this->pixel->y < 0 ||
			$this->pixel->x >= imagesx($imageResource) ||
			$this->pixel->y >= imagesy($imageResource)
		) {
			throw new PixelException(sprintf(
				'Pixel coordinates [ %d, %d ] are outside image bounds [ %d, %d ].',
				$this->pixel->x,
				$this->pixel->y,
				imagesx($imageResource),
				imagesy($imageResource),
			));
		}

		$color = imagecolorat($imageResource, $this->pixel->x, $this->pixel->y);

		$r = max(0, min(255, (int) round((($color >> 16) & 0xFF) / 16) * 16));
		$g = max(0, min(255, (int) round((($color >> 8) & 0xFF) / 16) * 16));
		$b = max(0, min(255, (int) round(($color & 0xFF) / 16) * 16));
		$a = 1 - ((($color & 0x7F000000) >> 24) / 127);

		return new Color($r, $g, $b, $a);
	}
}
