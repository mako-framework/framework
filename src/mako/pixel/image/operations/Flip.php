<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

/**
 * Flip.
 */
enum Flip
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
