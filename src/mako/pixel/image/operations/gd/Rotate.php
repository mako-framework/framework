<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use mako\pixel\image\operations\OperationInterface;
use Override;

use function getimagesize;
use function imagecolorallocatealpha;
use function imagecolortransparent;
use function imagecopy;
use function imagecreatetruecolor;
use function imagefill;
use function imagerotate;
use function imagesx;
use function imagesy;

/**
 * Rotates the image.
 */
class Rotate implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $degrees = 0
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
		if ($this->degrees === 0) {
			return;
		}

		$width = imagesx($imageResource);
		$height = imagesy($imageResource);

		$transparent = imagecolorallocatealpha($imageResource, 0, 0, 0, 127);

		if (getimagesize($imagePath)[2] === IMAGETYPE_GIF) {
			$temp = imagecreatetruecolor($width, $height);

			imagefill($temp, 0, 0, $transparent);

			imagecopy($temp, $imageResource, 0, 0, 0, 0, $width, $height);

			$imageResource = $temp;
		}

		$imageResource = imagerotate($imageResource, (360 - $this->degrees), $transparent);

		imagecolortransparent($imageResource, $transparent);
	}
}
