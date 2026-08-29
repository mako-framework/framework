<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\exceptions\ImageException;
use mako\pixel\image\operations\Flip as FlipOperation;
use mako\pixel\image\operations\FlipDirection;
use Override;

use function imagecolorallocatealpha;
use function imagecolortransparent;
use function imagecopy;
use function imagecreatetruecolor;
use function imagefill;
use function imagesx;
use function imagesy;

/**
 * {@inheritDoc}
 */
class Flip extends FlipOperation
{
	/**
	 * {@inheritDoc}
	 *
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$width = imagesx($imageResource);
		$height = imagesy($imageResource);

		$temp = imagecreatetruecolor($width, $height);

		if (!$temp) {
			throw new ImageException('Failed to create temporary image resource.');
		}

		$transparent = imagecolorallocatealpha($temp, 0, 0, 0, 127);

		imagefill($temp, 0, 0, $transparent);

		if ($this->direction ===  FlipDirection::Vertical) {
			for ($y = 0; $y < $height; $y++) {
				imagecopy($temp, $imageResource, 0, $y, 0, $height - $y - 1, $width, 1);
			}
		}
		else {
			for ($x = 0; $x < $width; $x++) {
				imagecopy($temp, $imageResource, $x, 0, $width - $x - 1, 0, 1, $height);
			}
		}

		imagecolortransparent($temp, $transparent);

		$imageResource = $temp;
	}
}
