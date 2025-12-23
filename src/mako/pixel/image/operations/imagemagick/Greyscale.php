<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\operations\OperationInterface;
use Override;

/**
 * Turns the image into greyscale.
 */
class Greyscale implements OperationInterface
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource, string $imagePath): void
	{
		$hasAlpha = $imageResource->getImageAlphaChannel();

		if ($hasAlpha) {
			$alpha = clone $imageResource;
		}

		$imageResource->setImageType(Imagick::IMGTYPE_GRAYSCALE);

		if ($hasAlpha) {
			$imageResource->compositeImage($alpha, Imagick::COMPOSITE_COPYOPACITY, 0, 0);

			$alpha->clear();
			$alpha->destroy();
		}
	}
}
