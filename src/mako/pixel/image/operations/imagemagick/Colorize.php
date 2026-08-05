<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use ImagickPixel;
use mako\pixel\image\Color;
use mako\pixel\image\operations\OperationInterface;
use Override;

/**
 * Colorizes the image.
 */
class Colorize implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Color $color
	) {
	}

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
