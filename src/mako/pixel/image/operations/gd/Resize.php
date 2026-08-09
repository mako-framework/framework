<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\Dimensions;
use mako\pixel\image\exceptions\ImageException;
use mako\pixel\image\operations\AspectRatio;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\traits\ResizeTrait;
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
	use ResizeTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Dimensions $dimensions,
		protected AspectRatio $aspectRatio = AspectRatio::Auto
	) {
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$oldWidth = imagesx($imageResource);
		$oldHeight = imagesy($imageResource);

		[$newWidth, $newHeight] = $this->calculateNewDimensions(
			$this->dimensions->width,
			$this->dimensions->height,
			$oldWidth,
			$oldHeight,
			$this->aspectRatio
		);

		$temp = imagecreatetruecolor($newWidth, $newHeight);

		if (!$temp) {
			throw new ImageException('Failed to create temporary image resource.');
		}

		$transparent = imagecolorallocatealpha($temp, 0, 0, 0, 127);

		imagefill($temp, 0, 0, $transparent);

		imagecopyresampled($temp, $imageResource, 0, 0, 0, 0, $newWidth, $newHeight, $oldWidth, $oldHeight);

		imagecolortransparent($temp, $transparent);

		$imageResource = $temp;
	}
}
