<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use mako\pixel\image\operations\Flip as FlipDirection;
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
 * Flips the image.
 */
class Flip implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected FlipDirection $direction = FlipDirection::HORIZONTAL
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
		$width = imagesx($imageResource);
		$height = imagesy($imageResource);

		$flipped = imagecreatetruecolor($width, $height);

		$transparent = imagecolorallocatealpha($flipped, 0, 0, 0, 127);

		imagefill($flipped, 0, 0, $transparent);

		if ($this->direction ===  FlipDirection::VERTICAL) {
			for ($y = 0; $y < $height; $y++) {
				imagecopy($flipped, $imageResource, 0, $y, 0, $height - $y - 1, $width, 1);
			}
		}
		else {
			for ($x = 0; $x < $width; $x++) {
				imagecopy($flipped, $imageResource, $x, 0, $width - $x - 1, 0, 1, $height);
			}
		}

		imagecolortransparent($flipped, $transparent);

		$imageResource = $flipped;
	}
}
