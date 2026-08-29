<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\operations\Sepia as BaseSepia;
use Override;

/**
 * {@inheritDoc}
 */
class Sepia extends BaseSepia
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
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
