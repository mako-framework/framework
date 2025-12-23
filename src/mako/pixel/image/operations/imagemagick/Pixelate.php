<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use mako\pixel\image\operations\OperationInterface;
use Override;

/**
 * Pixelates the image.
 */
class Pixelate implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $pixelSize = 10
	) {
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param \Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource, string $imagePath): void
	{
		$width = $imageResource->getImageWidth();
		$height = $imageResource->getImageHeight();

		$imageResource->scaleImage((int) ($width / $this->pixelSize), (int) ($height / $this->pixelSize));

		$imageResource->scaleImage($width, $height);
	}
}
