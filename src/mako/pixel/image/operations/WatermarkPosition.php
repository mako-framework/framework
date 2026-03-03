<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use Deprecated;

/**
 * Watermark position.
 */
enum WatermarkPosition
{
	/* Start compatibility */
	#[Deprecated('use WatermarkPosition::TopLeft instead', 'Mako 12.2.0')]
	public const TOP_LEFT = self::TopLeft;
	#[Deprecated('use WatermarkPosition::TopRight instead', 'Mako 12.2.0')]
	public const TOP_RIGHT = self::TopRight;
	#[Deprecated('use WatermarkPosition::BottomLeft instead', 'Mako 12.2.0')]
	public const BOTTOM_LEFT = self::BottomLeft;
	#[Deprecated('use WatermarkPosition::BottomRight instead', 'Mako 12.2.0')]
	public const BOTTOM_RIGHT = self::BottomRight;
	#[Deprecated('use WatermarkPosition::Center instead', 'Mako 12.2.0')]
	public const CENTER = self::Center;
	/* End compatibility */

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
