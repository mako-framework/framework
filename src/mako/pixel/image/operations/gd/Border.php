<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\Color;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\traits\GdTrait;
use Override;

use function imagerectangle;
use function imagesx;
use function imagesy;
use function max;

/**
 * Adds a border to the image.
 */
class Border implements OperationInterface
{
	use GdTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Color $color = new Color(0, 0, 0),
		protected int $width = 4
	) {
		$this->width = max(0, $this->width);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->width === 0) {
			return;
		}

		$width = imagesx($imageResource);
		$height = imagesy($imageResource);

		$color = $this->allocateColor($imageResource, $this->color);

		for ($i = 0; $i < $this->width; $i++) {
			$x = --$width;
			$y = --$height;

			imagerectangle($imageResource, $i, $i, $x, $y, $color);
		}
	}
}
