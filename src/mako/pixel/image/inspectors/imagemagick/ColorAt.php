<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\inspectors\imagemagick;

use Imagick;
use mako\pixel\image\Color;
use mako\pixel\image\exceptions\ImageException;
use mako\pixel\image\inspectors\InspectorInterface;
use mako\pixel\image\operations\Point;
use Override;

use function sprintf;

/**
 * Returns the color of the specified pixel.
 *
 * @implements InspectorInterface<Color>
 */
class ColorAt implements InspectorInterface
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
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function inspect(object &$imageResource): mixed
	{
		$width = $imageResource->getImageWidth();
		$height = $imageResource->getImageHeight();

		if (
			$this->pixel->x < 0 ||
			$this->pixel->y < 0 ||
			$this->pixel->x >= $width ||
			$this->pixel->y >= $height
		) {
			throw new ImageException(sprintf(
				'Pixel coordinates [ %d, %d ] are outside image bounds [ %d x %d ].',
				$this->pixel->x,
				$this->pixel->y,
				$width,
				$height,
			));
		}

		$pixel = $imageResource->getImagePixelColor($this->pixel->x, $this->pixel->y);

		$rgba = $pixel->getColor(2); // 2 = RGBA normalized to 0-255

		return new Color($rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
	}
}
