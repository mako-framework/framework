<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\Pixelate as BasePixelate;
use Override;

use function imagefilter;

/**
 * {@inheritDoc}
 */
class Pixelate extends BasePixelate
{
	/**
	 * {@inheritDoc}
	 *
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		imagefilter($imageResource, IMG_FILTER_PIXELATE, $this->pixelSize, IMG_FILTER_PIXELATE);
	}
}
