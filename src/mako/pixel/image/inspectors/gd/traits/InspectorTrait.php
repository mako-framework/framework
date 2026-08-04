<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\inspectors\gd\traits;

use GdImage;

use function imagealphablending;
use function imagecolorallocatealpha;
use function imagecopy;
use function imagecreatetruecolor;
use function imagefill;
use function imageistruecolor;
use function imagesavealpha;
use function round;

/**
 * Inspector trait.
 */
trait InspectorTrait
{
	/**
	 * Palette images (e.g. GIF) make imagecolorat() return palette indexes, not
	 * packed color values. In that case, this method creates a truecolor copy and
	 * preserves transparency so bit-shift RGBA extraction works consistently.
	 * Truecolor images are returned unchanged.
	 */
	protected function createTruecolorCopyIfNeeded(GdImage $imageResource, int $width, int $height, bool &$cloneCreated): GdImage
	{
		if (!imageistruecolor($imageResource)) {
			$image = imagecreatetruecolor($width, $height);

			imagealphablending($image, false);
			imagesavealpha($image, true);

			imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));

			imagecopy($image, $imageResource, 0, 0, 0, 0, $width, $height);

			$cloneCreated = true;

			return $image;
		}

		return $imageResource;
	}

	/**
	 * Converts a GD color to a RGBA array.
	 *
	 * @return array{0:int, 1:int, 2:int, 3:int}
	 */
	protected function convertColorToRgba(int $color): array
	{
		$r = ($color >> 16) & 0xFF;
		$g = ($color >> 8) & 0xFF;
		$b = $color & 0xFF;
		$a = (int) round((127 - (($color & 0x7F000000) >> 24)) / 127 * 255);

		return [$r, $g, $b, $a];
	}
}
