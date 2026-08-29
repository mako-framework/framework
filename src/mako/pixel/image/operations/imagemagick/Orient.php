<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use ImagickPixel;
use mako\pixel\image\operations\Orient as OrientOperation;
use Override;

/**
 * {@inheritDoc}
 */
class Orient extends OrientOperation
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		[$degrees, $flip, $flop] = match ($imageResource->getImageOrientation()) {
			Imagick::ORIENTATION_TOPRIGHT    => [0, false, true],    // 2: Flop
			Imagick::ORIENTATION_BOTTOMRIGHT => [180, false, false], // 3: Rotate 180
			Imagick::ORIENTATION_BOTTOMLEFT  => [0, true, false],    // 4: Flip
			Imagick::ORIENTATION_LEFTTOP     => [90, false, true],   // 5: Rotate 90 + Flop
			Imagick::ORIENTATION_RIGHTTOP    => [90, false, false],  // 6: Rotate 90
			Imagick::ORIENTATION_RIGHTBOTTOM => [90, true, false],   // 7: Rotate 90 + Flip
			Imagick::ORIENTATION_LEFTBOTTOM  => [270, false, false], // 8: Rotate 270
			default                          => [0, false, false],   // 1: nothing
		};

		if ($degrees !== 0) {
			$imageResource->rotateImage(new ImagickPixel('transparent'), $degrees);
		}

		if ($flip) {
			$imageResource->flipImage();
		}

		if ($flop) {
			$imageResource->flopImage();
		}

		$imageResource->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
	}
}
