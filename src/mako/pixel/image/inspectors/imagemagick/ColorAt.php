<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\inspectors\imagemagick;

use Imagick;
use mako\pixel\image\Color;
use mako\pixel\image\inspectors\InspectorInterface;
use mako\pixel\image\operations\Point;
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

		$pixel = $imageResource->getImagePixelColor($this->pixel->x, $this->pixel->y);

		$color = $pixel->getColor(1);

		$r = (int) round($color['r'] * 255);
		$g = (int) round($color['g'] * 255);
		$b = (int) round($color['b'] * 255);
		$a = (int) round($color['a'] * 255);

		return new Color($r, $g, $b, $a);
	}
}
