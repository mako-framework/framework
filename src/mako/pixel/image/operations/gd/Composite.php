<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\Gd;
use mako\pixel\image\operations\Composite as CompositeOperation;
use Override;

use function imagecopy;
use function imagesx;
use function imagesy;

/**
 * {@inheritDoc}
 *
 * @extends CompositeOperation<Gd>
 */
class Composite extends CompositeOperation
{
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
