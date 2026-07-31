<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use InvalidArgumentException;
use mako\pixel\image\exceptions\ImageException;
use mako\pixel\image\operations\OperationInterface;
use Override;

use function imagecolorallocatealpha;
use function imagecolortransparent;
use function imagecopyresampled;
use function imagecreatetruecolor;
use function imagefill;
use function imagesx;
use function imagesy;
use function round;

/**
 * Scales the image.
 */
class Scale implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $percent {
			set(int $value) {
				if ($value <= 0) {
					throw new InvalidArgumentException('Scale percentage must be greater than zero.');
				}
				$this->percent = $value;
			}
		},
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

		$newWidth = (int) round($oldWidth * ($this->percent / 100));
		$newHeight = (int) round($oldHeight * ($this->percent / 100));

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
