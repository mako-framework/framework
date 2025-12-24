<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use mako\pixel\image\operations\AspectRatio;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\traits\CalculateNewDimensionsTrait;
use Override;

use function imagecolorallocatealpha;
use function imagecolortransparent;
use function imagecopyresampled;
use function imagecreatetruecolor;
use function imagefill;
use function imagesx;
use function imagesy;

/**
 * Resizes the image.
 */
class Resize implements OperationInterface
{
	use CalculateNewDimensionsTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $width,
		protected ?int $height = null,
		protected AspectRatio $aspectRatio = AspectRatio::AUTO
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

		[$newWidth, $newHeight] = $this->calculateNewDimensions($this->width, $this->height, $oldWidth, $oldHeight, $this->aspectRatio);

		$resized = imagecreatetruecolor($newWidth, $newHeight);

		$transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);

		imagefill($resized, 0, 0, $transparent);

		imagecopyresampled($resized, $imageResource, 0, 0, 0, 0, $newWidth, $newHeight, $oldWidth, $oldHeight);

		imagecolortransparent($resized, $transparent);

		$imageResource = $resized;
	}
}
