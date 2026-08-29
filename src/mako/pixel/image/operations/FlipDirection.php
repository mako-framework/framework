<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

/**
 * Flip direction.
 */
enum FlipDirection
{
	/**
	 * Flip horizontally.
	 */
	case Horizontal;

	/**
	 * Flip vertically.
	 */
	case Vertical;
}
