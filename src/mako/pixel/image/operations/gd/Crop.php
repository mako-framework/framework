<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use mako\pixel\image\exceptions\ImageException;
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
	public function apply(object &$imageResource): void
	{
		$oldWidth = imagesx($imageResource);
		$oldHeight = imagesy($imageResource);

		$temp = imagecreatetruecolor($this->width, $this->height);

		if (!$temp) {
			throw new ImageException('Failed to create temporary image resource.');
		}

		$transparent = imagecolorallocatealpha($temp, 0, 0, 0, 127);

		imagefill($temp, 0, 0, $transparent);

		imagecopy($temp, $imageResource, 0, 0, $this->x, $this->y, $oldWidth, $oldHeight);

		imagecolortransparent($temp, $transparent);

		$imageResource = $temp;
	}
}
