<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\inspectors\gd;

use GdImage;
use mako\pixel\image\Color;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\inspectors\InspectorInterface;
use mako\pixel\image\traits\GdTrait;
use mako\pixel\image\traits\PixelValidationTrait;
use Override;

use function imagecolorat;
use function imagesx;
use function imagesy;

/**
 * Returns the color of the specified pixel.
 *
 * @implements InspectorInterface<Color>
 */
class ColorAt implements InspectorInterface
{
	use GdTrait;
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
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function inspect(object &$imageResource): mixed
	{
		$width = imagesx($imageResource);
		$height = imagesy($imageResource);

		$this->validatePixel($this->pixel, $width, $height);

		// Ensure truecolor image for accurate colors

		$image = $this->createTruecolorCopyIfNeeded($imageResource, $width, $height, $copyCreated);

		// Extract color

		$color = imagecolorat($image, $this->pixel->x, $this->pixel->y);

		// Destroy copy if one was created

		if ($copyCreated) {
			$image = null;
		}

		// Return color

		[$r, $g, $b, $a] = $this->convertColorToRgba($color);

		return new Color($r, $g, $b, $a);
	}
}
