<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use mako\pixel\image\geometry\Dimensions;

/**
 * Resizes the image.
 */
abstract class Resize implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected Dimensions $dimensions,
		protected AspectRatio $aspectRatio = AspectRatio::Auto
	) {
	}
}
