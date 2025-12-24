<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use ImagickDraw;
use ImagickPixel;
use mako\pixel\image\Color;
use mako\pixel\image\operations\OperationInterface;
use Override;

/**
 * Adds a border to the image.
 */
class Border implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Color $color = new Color(0, 0, 0),
		protected int $thickness = 5
	) {
	}

	/**
	 * Does the image support alpha?
	 */
	protected function supportsAlpha($imageResource): bool
	{
		return match (strtolower($imageResource->getImageFormat())) {
			'gif' => false,
			default => true,
		};
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param \Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource, string $imagePath): void
	{
		$draw = new ImagickDraw;

		$colorString = $this->supportsAlpha($imageResource) ? $this->color->toRgbaString() : $this->color->toRgbString();

		$draw->setStrokeColor(new ImagickPixel($colorString));
		$draw->setStrokeWidth($this->thickness);
		$draw->setFillOpacity(0);
		$draw->setStrokeAntialias(true);

		$width = $imageResource->getImageWidth();
		$height = $imageResource->getImageHeight();

		$draw->rectangle(
			$this->thickness / 2,
			$this->thickness / 2,
			$width - $this->thickness / 2,
			$height - $this->thickness / 2
		);

		$imageResource->drawImage($draw);

		$draw->clear();
		$draw->destroy();
	}
}
