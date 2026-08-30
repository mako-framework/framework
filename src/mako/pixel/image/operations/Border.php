<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use InvalidArgumentException;
use mako\pixel\image\Color;

/**
 * Adds a border to the image.
 */
abstract class Border implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected Color $color = new Color(0, 0, 0),
		protected int $width = 4
	) {
		if ($width < 0) {
			throw new InvalidArgumentException('The border width must be a non-negative number.');
		}
	}
}
