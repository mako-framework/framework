<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\file\image\traits;

use SplFileInfo;

use function getimagesize;

/**
 * Image size trait.
 *
 * @author Frederic G. Østby
 */
trait GetImageSizeTrait
{
	/**
	 * Returns the image size.
	 *
	 * @param  \SplFileInfo $image Image file
	 * @return array
	 */
	protected function getImageSize(SplFileInfo $image): array
	{
		return getimagesize($image->getPathname());
	}
}
