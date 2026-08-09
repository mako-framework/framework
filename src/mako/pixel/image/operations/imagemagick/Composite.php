<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\ImageMagick;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\Point;
use Override;

/**
 * Composites an image onto the image at the specified position.
 */
class Composite implements OperationInterface
{
	public function __construct(
		protected ImageMagick $image,
		protected Point $position = new Point(0, 0)
	) {
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$imageResource->compositeImage(
			$this->image->getImageResource(),
			Imagick::COMPOSITE_OVER,
			$this->position->x,
			$this->position->y
		);
	}
}
