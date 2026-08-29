<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\operations\Crop as BaseCrop;
use Override;

/**
 * {@inheritDoc}
 */
class Crop extends BaseCrop
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$imageResource->cropImage($this->dimensions->width, $this->dimensions->height, $this->position->x, $this->position->y);
	}
}
