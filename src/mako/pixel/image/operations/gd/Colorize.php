<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use mako\pixel\image\Color;
use mako\pixel\image\operations\OperationInterface;
use Override;

use function imagecolorallocate;
use function imagecolorat;
use function imagecreatetruecolor;
use function imagesetpixel;
use function imagesx;
use function imagesy;
use function max;
use function min;

/**
 * Colorizes the image.
 */
class Colorize implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Color $color
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
		$colors = [
			'r' => $this->color->getRed(),
			'g' => $this->color->getGreen(),
			'b' => $this->color->getBlue(),
		];

		$width = imagesx($imageResource);
		$height = imagesy($imageResource);

		$temp = imagecreatetruecolor($width, $height);

		for ($x = 0; $x < $width; $x++) {
			for ($y = 0; $y < $height; $y++) {
				$rgb = imagecolorat($imageResource, $x, $y);

				imagesetpixel($temp, $x, $y, imagecolorallocate(
					$temp,
					max(0, min(255, (($rgb >> 16) & 0xFF) + $colors['r'])), // R
					max(0, min(255, (($rgb >> 8) & 0xFF) + $colors['g'])),  // G
					max(0, min(255, ($rgb & 0xFF) + $colors['b']))          // B
				));
			}
		}

		$imageResource = $temp;
	}
}
