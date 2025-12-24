<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use mako\pixel\image\operations\OperationInterface;
use Override;

use function imagecolorallocatealpha;
use function imagecolorat;
use function imagecreatetruecolor;
use function imagesetpixel;
use function imagesx;
use function imagesy;
use function max;
use function min;

/**
 * Turns the image into sepia.
 */
class Sepia implements OperationInterface
{
	/**
	 * {@inheritDoc}
	 *
	 * @param \GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$width = imagesx($imageResource);
		$height = imagesy($imageResource);

		$temp = imagecreatetruecolor($width, $height);

		for ($x = 0; $x < $width; $x++) {
			for ($y = 0; $y < $height; $y++) {
				$rgb = imagecolorat($imageResource, $x, $y);

				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;

				imagesetpixel($temp, $x, $y, imagecolorallocatealpha(
					$temp,
					max(0, min(255, ($r * 0.393 + $g * 0.769 + $b * 0.189) * 0.85)), // R
					max(0, min(255, ($r * 0.349 + $g * 0.686 + $b * 0.168) * 0.85)), // G
					max(0, min(255, ($r * 0.272 + $g * 0.534 + $b * 0.131) * 0.85)), // B
					($rgb >> 24) & 0x7F                                              // A
				));
			}
		}

		$imageResource = $temp;
	}
}
