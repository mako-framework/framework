<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

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
	 * @param \GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$sharpen = [[-1.2, -1, -1.2], [-1, 20, -1], [-1.2, -1, -1.2]];

		$divisor = array_sum(array_map(array_sum(...), $sharpen));

		imageconvolution($imageResource, $sharpen, $divisor, 0);
	}
}
