<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\inspectors\gd;

use GdImage;
use mako\pixel\image\Color;
use mako\pixel\image\exceptions\ImageException;
use mako\pixel\image\inspectors\gd\traits\InspectorTrait;
use mako\pixel\image\inspectors\InspectorInterface;
use mako\pixel\image\operations\Point;
use Override;

use function imagecolorat;
use function imagesx;
use function imagesy;
use function sprintf;

/**
 * Returns the color of the specified pixel.
 *
 * @implements InspectorInterface<Color>
 */
class PixelColor implements InspectorInterface
{
	use InspectorTrait;

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

		// Ensure truecolor image

		$cloneCreated = false;

		$image = $this->createTruecolorCopyIfNeeded($imageResource, $width, $height, $cloneCreated);

		// Extract color

		$color = imagecolorat($image, $this->pixel->x, $this->pixel->y);

		// Destroy clone if one was created

		if ($cloneCreated) {
			$image = null;
		}

		// Return color

		[$r, $g, $b, $a] = $this->convertColorToRgba($color);

		return new Color($r, $g, $b, $a);
	}
}
