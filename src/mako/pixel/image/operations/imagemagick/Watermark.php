<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\ImageMagick;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\traits\NormalizeTrait;
use mako\pixel\image\operations\WatermarkPosition;
use Override;

/**
 * Adds a watermark to the image.
 */
class Watermark implements OperationInterface
{
	use NormalizeTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected ImageMagick|string $image,
		protected WatermarkPosition $position = WatermarkPosition::BottomRight,
		protected int $opacity = 100,
		protected int $margin = 0
	) {
		if ($image instanceof ImageMagick === false) {
			$this->image = new ImageMagick($image);
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->opacity < 100) {
			$this->image->apply(new Opacity($this->opacity));
		}

		$point = $this->position->resolvePosition(
			new Dimensions($imageResource->getImageWidth(), $imageResource->getImageHeight()),
			$this->image->getDimensions(),
			$this->margin
		);

		new Composite($this->image, $point)->apply($imageResource);
	}
}
