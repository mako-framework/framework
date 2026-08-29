<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\ImageMagick;
use mako\pixel\image\operations\Composite as BaseComposite;
use Override;

/**
 * {@inheritDoc}
 *
 * @extends BaseComposite<ImageMagick>
 */
class Composite extends BaseComposite
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$imageResource->compositeImage(
			$this->image->getImageResource(),
			Imagick::COMPOSITE_OVER,
			$this->position->x,
			$this->position->y
		);
	}
}
