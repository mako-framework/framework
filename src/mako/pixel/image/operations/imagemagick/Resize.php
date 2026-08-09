<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\Dimensions;
use mako\pixel\image\operations\AspectRatio;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\traits\ResizeTrait;
use Override;

/**
 * Resizes the image.
 */
class Resize implements OperationInterface
{
	use ResizeTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Dimensions $dimensions,
		protected AspectRatio $aspectRatio = AspectRatio::Auto
	) {
	}

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
