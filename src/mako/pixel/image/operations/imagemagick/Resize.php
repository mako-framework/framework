<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\operations\Resize as ResizeOperation;
use mako\pixel\image\operations\traits\ResizeTrait;
use Override;

/**
 * {@inheritDoc}
 */
class Resize extends ResizeOperation
{
	use ResizeTrait;

	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		[$newWidth, $newHeight] = $this->calculateNewDimensions(
			$this->dimensions->width,
			$this->dimensions->height,
			$imageResource->getImageWidth(),
			$imageResource->getImageHeight(),
			$this->aspectRatio
		);

		$imageResource->scaleImage($newWidth, $newHeight);
	}
}
