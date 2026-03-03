<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use mako\pixel\image\operations\Flip as FlipDirection;
use mako\pixel\image\operations\OperationInterface;
use Override;

/**
 * Flips the image.
 */
class Flip implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected FlipDirection $direction = FlipDirection::Horizontal
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
		if ($this->direction ===  FlipDirection::Vertical) {
			$imageResource->flipImage();
		}
		else {
			$imageResource->flopImage();
		}
	}
}
