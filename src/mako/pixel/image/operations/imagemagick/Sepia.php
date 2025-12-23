<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use mako\pixel\image\operations\OperationInterface;
use Override;

/**
 * Turns the image into sepia.
 */
class Sepia implements OperationInterface
{
	/**
	 * {@inheritDoc}
	 *
	 * @param \Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource, string $imagePath): void
	{
		$imageResource->colorMatrixImage([
			0.393 * 0.85, 0.769 * 0.85, 0.189 * 0.85, 0, 0,
			0.349 * 0.85, 0.686 * 0.85, 0.168 * 0.85, 0, 0,
			0.272 * 0.85, 0.534 * 0.85, 0.131 * 0.85, 0, 0,
			0,            0,            0,            1, 0,
			0,            0,            0,            0, 1,
		]);
	}
}
