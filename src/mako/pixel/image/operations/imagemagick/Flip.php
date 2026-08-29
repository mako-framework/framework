<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\operations\Flip as BaseFlip;
use mako\pixel\image\operations\FlipDirection;
use Override;

/**
 * {@inheritDoc}
 */
class Flip extends BaseFlip
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->direction ===  FlipDirection::Vertical) {
			$imageResource->flipImage();
		}
		else {
			$imageResource->flopImage();
		}
	}
}
