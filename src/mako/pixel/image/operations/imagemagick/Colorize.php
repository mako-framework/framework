<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use ImagickPixel;
use mako\pixel\image\operations\Colorize as BaseColorize;
use Override;

/**
 * {@inheritDoc}
 */
class Colorize extends BaseColorize
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$pixel = new ImagickPixel($this->color->toHexaString());

		$imageResource->colorizeImage($pixel, $pixel);
	}
}
