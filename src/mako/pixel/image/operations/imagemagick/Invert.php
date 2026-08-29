<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\operations\Invert as BaseInvert;
use Override;

/**
 * {@inheritDoc}
 */
class Invert extends BaseInvert
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$alpha = null;

		if ($imageResource->getImageAlphaChannel()) {
			$alpha = clone $imageResource;
		}

		$imageResource->negateImage(false);

		if ($alpha !== null) {
			$imageResource->compositeImage($alpha, Imagick::COMPOSITE_COPYOPACITY, 0, 0);

			$alpha->clear();
			$alpha->destroy();
		}
	}
}
