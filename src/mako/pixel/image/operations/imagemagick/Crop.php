<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use mako\pixel\image\operations\OperationInterface;
use Override;

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
	 * @param \Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$imageResource->cropImage($this->width, $this->height, $this->x, $this->y);
	}
}
