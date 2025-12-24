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
use function imagecolorat;
use function imagecreatetruecolor;
use function imagefill;
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
	public function apply(object &$imageResource): void
	{
		$colors = [
			'r' => $this->color->getRed(),
			'g' => $this->color->getGreen(),
			'b' => $this->color->getBlue(),
		];

		$width = imagesx($imageResource);
		$height = imagesy($imageResource);

		$temp = imagecreatetruecolor($width, $height);

		imagefill($temp, 0, 0, imagecolorallocatealpha($temp, 0, 0, 0, 127));

		for ($x = 0; $x < $width; $x++) {
			for ($y = 0; $y < $height; $y++) {
				$rgb = imagecolorat($imageResource, $x, $y);

				$a = ($rgb >> 24) & 0x7F;

				if ($a === 127) {
					continue;
				}

				imagesetpixel($temp, $x, $y, imagecolorallocatealpha(
					$temp,
					max(0, min(255, (($rgb >> 16) & 0xFF) + $colors['r'])), // R
					max(0, min(255, (($rgb >> 8) & 0xFF) + $colors['g'])),  // G
					max(0, min(255, ($rgb & 0xFF) + $colors['b'])),         // B
					$a                                                      // A
				));
			}
		}

		$imageResource = $temp;
	}
}
