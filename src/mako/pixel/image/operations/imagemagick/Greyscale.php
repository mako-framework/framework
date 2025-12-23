<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\operations\OperationInterface;
use Override;

/**
 * Turns the image into greyscale.
 */
class Greyscale implements OperationInterface
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource, string $imagePath): void
	{
		$imageResource->setImageType(Imagick::IMGTYPE_GRAYSCALE);
	}
}
