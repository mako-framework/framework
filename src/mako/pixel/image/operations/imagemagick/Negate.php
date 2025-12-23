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
 * Negates the image.
 */
class Negate implements OperationInterface
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource, string $imagePath): void
	{
		$alpha = clone $imageResource;

		$imageResource->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);

		$imageResource->negateImage(false);

		$imageResource->compositeImage($alpha, Imagick::COMPOSITE_COPYOPACITY, 0, 0);

		$alpha->clear();
		$alpha->destroy();
	}
}
