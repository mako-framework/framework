<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use mako\pixel\image\geometry\Point;

/**
 * Draws text on the image.
 */
abstract class Text implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected string $text,
		protected Font $font,
		protected Point $position,
	) {
	}
}
