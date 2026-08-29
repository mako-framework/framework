<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\operations\Scale as ScaleOperation;
use Override;

use function round;

/**
 * {@inheritDoc}
 */
class Scale extends ScaleOperation
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$oldWidth = $imageResource->getImageWidth();
		$oldHeight = $imageResource->getImageHeight();

		$newWidth = (int) round($oldWidth * ($this->percent / 100));
		$newHeight = (int) round($oldHeight * ($this->percent / 100));

		$imageResource->scaleImage($newWidth, $newHeight);
	}
}
