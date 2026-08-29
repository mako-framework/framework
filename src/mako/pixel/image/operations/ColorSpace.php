<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use mako\pixel\image\ColorSpace as ColorSpaceEnum;

/**
 * Transforms the color space of the image.
 */
abstract class ColorSpace implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected ColorSpaceEnum $colorSpace
	) {
	}
}
