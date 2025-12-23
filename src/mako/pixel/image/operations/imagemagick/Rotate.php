<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use ImagickPixel;
use mako\pixel\image\operations\OperationInterface;
use Override;

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
	 * @param \Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource, string $imagePath): void
	{
		if ($this->degrees === 0) {
			return;
		}

		$imageResource->rotateImage(new ImagickPixel('none'), $this->degrees);
	}
}
