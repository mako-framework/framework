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
	case TopLeft;

	/**
	 * Top right corner.
	 */
	case TopRight;

	/**
	 * Bottom left corner.
	 */
	case BottomLeft;

	/**
	 * Bottom right corner.
	 */
	case BottomRight;

	/**
	 * Centered.
	 */
	case Center;
}
