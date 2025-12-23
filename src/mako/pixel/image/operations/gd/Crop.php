<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use mako\pixel\image\operations\OperationInterface;
use Override;

use function imagecolorallocatealpha;
use function imagecolortransparent;
use function imagecopy;
use function imagecreatetruecolor;
use function imagefill;
use function imagesx;
use function imagesy;

/**
 * Crops the image.
 */
class Crop implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $width,
		protected int $height,
		protected int $x,
		protected int $y
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
		$oldWidth = imagesx($imageResource);
		$oldHeight = imagesy($imageResource);

		$crop = imagecreatetruecolor($this->width, $this->height);

		$transparent = imagecolorallocatealpha($crop, 0, 0, 0, 127);

		imagefill($crop, 0, 0, $transparent);

		imagecopy($crop, $imageResource, 0, 0, $this->x, $this->y, $oldWidth, $oldHeight);

		imagecolortransparent($crop, $transparent);

		$imageResource = $crop;
	}
}
