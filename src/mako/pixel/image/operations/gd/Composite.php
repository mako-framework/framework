<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\Gd;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\Point;
use Override;

use function imagecopy;
use function imagesx;
use function imagesy;

/**
 * Composites an image onto the image at the specified position.
 */
class Composite implements OperationInterface
{
	public function __construct(
		protected Gd $image,
		protected Point $position = new Point(0, 0)
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
		$composite = $this->image->getImageResource();

		imagecopy(
			$imageResource,
			$composite,
			$this->position->x,
			$this->position->y,
			0,
			0,
			imagesx($composite),
			imagesy($composite)
		);
	}
}
