<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use mako\pixel\image\operations\OperationInterface;
use Override;

use function imagefilter;

/**
 * Turns the image into bitonal.
 */
class Bitonal implements OperationInterface
{
	/**
	 * {@inheritDoc}
	 *
	 * @param \GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		imagefilter($imageResource, IMG_FILTER_GRAYSCALE);
		imagefilter($imageResource, IMG_FILTER_CONTRAST, -2000);
	}
}
