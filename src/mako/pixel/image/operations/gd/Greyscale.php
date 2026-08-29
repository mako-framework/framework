<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\Greyscale as BaseGreyscale;
use Override;

use function imagefilter;

/**
 * {@inheritDoc}
 */
class Greyscale extends BaseGreyscale
{
	/**
	 * {@inheritDoc}
	 *
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		imagefilter($imageResource, IMG_FILTER_GRAYSCALE);
	}
}
