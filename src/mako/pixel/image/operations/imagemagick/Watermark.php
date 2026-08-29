<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\ImageMagick;
use mako\pixel\image\operations\Watermark as BaseWatermark;
use Override;

/**
 * {@inheritDoc}
 *
 * @property ImageMagick $image
 */
class Watermark extends BaseWatermark
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function getImageClass(): string
	{
		return ImageMagick::class;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->opacity < 100) {
			$this->image->apply(new Opacity($this->opacity));
		}

		$point = $this->position->resolvePosition(
			new Dimensions($imageResource->getImageWidth(), $imageResource->getImageHeight()),
			$this->image->getDimensions(),
			$this->margin
		);

		new Composite($this->image, $point)->apply($imageResource);
	}
}
