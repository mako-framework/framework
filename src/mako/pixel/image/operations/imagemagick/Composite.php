<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\ImageMagick;
use mako\pixel\image\operations\Composite as CompositeOperation;
use Override;

/**
 * {@inheritDoc}
 *
 * @extends CompositeOperation<ImageMagick>
 */
class Composite extends CompositeOperation
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
