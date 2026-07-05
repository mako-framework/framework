<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

/**
 * Aspect ratio.
 */
enum AspectRatio
{
	/**
	 * Ignore the aspect ratio.
	 */
	case Ignore;

	/**
	 * Calculate smallest size based on given height and width while maintaining aspect ratio.
	 */
	case Auto;

	/**
	 * Calculate new size on given width while maintaining aspect ratio.
	 */
	case Width;

	/**
	 * Calculate new size on given height while maintaining aspect ratio.
	 */
	case Height;
}
