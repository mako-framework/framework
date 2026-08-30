<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\operations\Saturation as SaturationOperation;
use Override;

/**
 * {@inheritDoc}
 */
class Saturation extends SaturationOperation
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->level === 0) {
			return;
		}

		$saturation = 100 + $this->level;

		$imageResource->modulateImage(100, $saturation, 100);
	}
}
