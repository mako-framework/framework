<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\Gamma as BaseGamma;
use Override;

use function imagegammacorrect;

/**
 * {@inheritDoc}
 */
class Gamma extends BaseGamma
{
	/**
	 * {@inheritDoc}
	 *
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->gamma === 1.0) {
			return;
		}

		imagegammacorrect($imageResource, 1.0, $this->gamma);
	}
}
