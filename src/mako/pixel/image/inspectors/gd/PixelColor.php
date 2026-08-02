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
		if (
			$this->pixel->x < 0 ||
			$this->pixel->y < 0 ||
			$this->pixel->x >= imagesx($imageResource) ||
			$this->pixel->y >= imagesy($imageResource)
		) {
			throw new ImageException(sprintf(
				'Pixel coordinates [ %d, %d ] are outside image bounds [ %d x %d ].',
				$this->pixel->x,
				$this->pixel->y,
				imagesx($imageResource),
				imagesy($imageResource),
			));
		}

		$color = imagecolorat($imageResource, $this->pixel->x, $this->pixel->y);

		[$r, $g, $b, $a] = $this->convertColorToRgba($color);

		return new Color($r, $g, $b, $a);
	}
}
