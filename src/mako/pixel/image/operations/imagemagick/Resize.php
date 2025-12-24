<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use mako\pixel\image\operations\AspectRatio;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\traits\CalculateNewDimensionsTrait;
use Override;

/**
 * Resizes the image.
 */
class Resize implements OperationInterface
{
	use CalculateNewDimensionsTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $width,
		protected ?int $height = null,
		protected AspectRatio $aspectRatio = AspectRatio::AUTO
	) {
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param \Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$oldWidth = $imageResource->getImageWidth();
		$oldHeight = $imageResource->getImageHeight();

		[$newWidth, $newHeight] = $this->calculateNewDimensions($this->width, $this->height, $oldWidth, $oldHeight, $this->aspectRatio);

		$imageResource->scaleImage($newWidth, $newHeight);
	}
}
