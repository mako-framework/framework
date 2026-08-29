<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\exceptions\ImageException;
use mako\pixel\image\operations\Rotate as BaseRotate;
use Override;

use function imagecolorallocatealpha;
use function imagecolortransparent;
use function imagerotate;

/**
 * {@inheritDoc}
 */
class Rotate extends BaseRotate
{
	/**
	 * {@inheritDoc}
	 *
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->degrees === 0) {
			return;
		}

		$transparent = imagecolorallocatealpha($imageResource, 0, 0, 0, 127);

		$temp = imagerotate($imageResource, (360 - $this->degrees), $transparent);

		if (!$temp) {
			throw new ImageException('Failed to create temporary image resource.');
		}

		$imageResource = $temp;

		imagecolortransparent($imageResource, $transparent);
	}
}
