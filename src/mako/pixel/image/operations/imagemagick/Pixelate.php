<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\operations\Pixelate as PixelateOperation;
use Override;

/**
 * {@inheritDoc}
 */
class Pixelate extends PixelateOperation
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$width = $imageResource->getImageWidth();
		$height = $imageResource->getImageHeight();

		$imageResource->scaleImage((int) ($width / $this->pixelSize), (int) ($height / $this->pixelSize));

		$imageResource->scaleImage($width, $height);
	}
}
