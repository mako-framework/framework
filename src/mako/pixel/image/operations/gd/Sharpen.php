<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\OperationInterface;
use Override;

use function array_map;
use function array_sum;
use function imageconvolution;

/**
 * Sharpens the image.
 */
class Sharpen implements OperationInterface
{
	/**
	 * {@inheritDoc}
	 *
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$sharpen = [[-2, -1.6, -2], [-1.6, 22, -1.6], [-2, -1.6, -2]];

		$divisor = array_sum(array_map(array_sum(...), $sharpen));

		imageconvolution($imageResource, $sharpen, $divisor, 0);
	}
}
