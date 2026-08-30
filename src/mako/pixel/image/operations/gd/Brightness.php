<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\Brightness as BrightnessOperation;
use Override;

use function imagefilter;

/**
 * {@inheritDoc}
 */
class Brightness extends BrightnessOperation
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

		imagefilter($imageResource, IMG_FILTER_BRIGHTNESS, $this->level);
	}
}
