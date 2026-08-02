<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\inspectors\imagemagick;

use Imagick;
use mako\pixel\exceptions\PixelException;
use mako\pixel\image\Color;
use mako\pixel\image\inspectors\InspectorInterface;
use mako\pixel\image\operations\Point;
use Override;

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
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function inspect(object &$imageResource): mixed
	{
		if (
			$this->pixel->x < 0 ||
			$this->pixel->y < 0 ||
			$this->pixel->x >= $imageResource->getImageWidth() ||
			$this->pixel->y >= $imageResource->getImageHeight()
		) {
			throw new PixelException(sprintf(
				'Pixel coordinates [ %d, %d ] are outside image bounds [ %d, %d ].',
				$this->pixel->x,
				$this->pixel->y,
				$imageResource->getImageWidth(),
				$imageResource->getImageHeight(),
			));
		}

		$pixel = $imageResource->getImagePixelColor($this->pixel->x, $this->pixel->y);

		$rgba = $pixel->getColor(2); // 2 = RGBA normalized to 0-255

		return new Color($rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
	}
}
