<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\geometry\Point;

/**
 * Crops the image.
 */
abstract class Crop implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected Dimensions $dimensions,
		protected Point $position
	) {
	}
}
