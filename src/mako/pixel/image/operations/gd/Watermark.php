<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\Gd;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\operations\Watermark as BaseWatermark;
use Override;

use function imagesx;
use function imagesy;

/**
 * {@inheritDoc}
 *
 * @extends BaseWatermark<Gd>
 */
class Watermark extends BaseWatermark
{
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
