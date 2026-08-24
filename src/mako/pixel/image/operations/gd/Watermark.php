<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\Gd;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\WatermarkPosition;
use Override;

use function imagesx;
use function imagesy;

/**
 * Adds a watermark to the image.
 */
class Watermark implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Gd|string $image,
		protected WatermarkPosition $position = WatermarkPosition::BottomRight,
		protected int $opacity = 100,
		protected int $margin = 0
	) {
		if ($image instanceof Gd === false) {
			$this->image = new Gd($image);
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->opacity < 100) {
			$this->image->apply(new Opacity($this->opacity));
		}

		$point = $this->position->resolvePosition(
			new Dimensions(imagesx($imageResource), imagesy($imageResource)),
			$this->image->getDimensions(),
			$this->margin
		);

		new Composite($this->image, $point)->apply($imageResource);
	}
}
