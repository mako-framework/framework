<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

/**
 * Watermark position.
 */
enum WatermarkPosition
{
	/**
	 * Top left corner.
	 */
	case TOP_LEFT;

	/**
	 * Top right corner.
	 */
	case TOP_RIGHT;

	/**
	 * Bottom left corner.
	 */
	case BOTTOM_LEFT;

	/**
	 * Bottom left corner.
	 */
	case BOTTOM_RIGHT;

	/**
	 * Centered.
	 */
	case CENTER;
}
