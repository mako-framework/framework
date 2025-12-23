<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use mako\pixel\image\Color;
use mako\pixel\image\operations\OperationInterface;
use Override;

use function imagecolorallocatealpha;
use function imagerectangle;
use function imagesx;
use function imagesy;
use function round;

/**
 * Adds a border to the image.
 */
class Border implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Color $color = new Color(0, 0, 0),
		protected int $thickness = 5
	) {
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param \GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource, string $imagePath): void
	{
		$width = imagesx($imageResource);
		$height = imagesy($imageResource);

		$r = $this->color->getRed();
		$g = $this->color->getGreen();
		$b = $this->color->getBlue();

		$alhpa = 127 - (int) round($this->color->getAlpha() * 127 / 255);

		$color = imagecolorallocatealpha($imageResource, $r, $g, $b, $alhpa);

		for ($i = 0; $i < $this->thickness; $i++) {
			$x = --$width;
			$y = --$height;

			imagerectangle($imageResource, $i, $i, $x, $y, $color);
		}
	}
}
