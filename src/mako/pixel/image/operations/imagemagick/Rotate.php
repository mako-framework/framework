<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use ImagickPixel;
use mako\pixel\image\operations\Rotate as BaseRotate;
use Override;

/**
 * {@inheritDoc}
 */
class Rotate extends BaseRotate
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->degrees === 0) {
			return;
		}

		$imageResource->rotateImage(new ImagickPixel('transparent'), $this->degrees);
	}
}
