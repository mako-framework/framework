<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\Contrast as ContrastOperation;
use Override;

use function imagefilter;

/**
 * {@inheritDoc}
 */
class Contrast extends ContrastOperation
{
	/**
	 * {@inheritDoc}
	 *
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->level === 0) {
			return;
		}

		imagefilter(
			$imageResource,
			IMG_FILTER_CONTRAST,
			-$this->level // negative = more contrast
		);
	}
}
