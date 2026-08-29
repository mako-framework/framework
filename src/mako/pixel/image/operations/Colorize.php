<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use mako\pixel\image\Color;

/**
 * Colorizes the image.
 */
abstract class Colorize implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected Color $color
	) {
	}
}
