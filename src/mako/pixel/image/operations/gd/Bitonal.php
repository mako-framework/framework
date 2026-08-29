<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\Bitonal as BaseBitonal;
use Override;

use function imagefilter;

/**
 * {@inheritDoc}
 */
class Bitonal extends BaseBitonal
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
		imagefilter($imageResource, IMG_FILTER_CONTRAST, -2000);
	}
}
