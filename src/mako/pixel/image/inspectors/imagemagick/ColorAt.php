<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\inspectors\imagemagick;

use Imagick;
use mako\pixel\image\Color;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\inspectors\InspectorInterface;
use mako\pixel\image\traits\PixelValidationTrait;
use Override;

/**
 * Returns the color of the specified pixel.
 *
 * @implements InspectorInterface<Color>
 */
class ColorAt implements InspectorInterface
{
	use PixelValidationTrait;

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
		$this->validatePixel(
			$this->pixel,
			$imageResource->getImageWidth(),
			$imageResource->getImageHeight()
		);

		$needsConversion = $imageResource->getImageColorspace() !== Imagick::COLORSPACE_SRGB;

		$image = $needsConversion ? clone $imageResource : $imageResource;

		if ($needsConversion) {
			$image->transformImageColorspace(Imagick::COLORSPACE_SRGB);
		}

		$pixel = $image->getImagePixelColor($this->pixel->x, $this->pixel->y);

		$rgba = $pixel->getColor(2); // 2 = RGBA normalized to 0-255

		if ($needsConversion) {
			$image->clear();
			$image->destroy();
		}

		return new Color($rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
	}
}
