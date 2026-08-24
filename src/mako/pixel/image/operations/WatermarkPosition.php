<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\geometry\Position;

/**
 * Defines the position of a watermark within a container.
 */
enum WatermarkPosition
{
	/**
	 * Top left.
	 */
	case TopLeft;

	/**
	 * Top.
	 */
	case Top;

	/**
	 * Top right.
	 */
	case TopRight;

	/**
	 * Center left.
	 */
	case CenterLeft;

	/**
	 * Center.
	 */
	case Center;

	/**
	 * Center right.
	 */
	case CenterRight;

	/**
	 * Bottom.
	 */
	case Bottom;

	/**
	 * Bottom left.
	 */
	case BottomLeft;

	/**
	 * Bottom right.
	 */
	case BottomRight;

	/**
	 * Resolves the watermark position into the top-left coordinates required
	 * to place the watermark within a container.
	 */
	public function resolvePosition(Dimensions $container, Dimensions $watermark, int $margin = 0): Point
	{
		return match ($this) {
			self::TopLeft => Position::topLeft($container, $watermark, $margin),
			self::Top => Position::top($container, $watermark, $margin),
			self::TopRight => Position::topRight($container, $watermark, $margin),
			self::CenterLeft => Position::centerLeft($container, $watermark, $margin),
			self::Center => Position::center($container, $watermark),
			self::CenterRight => Position::centerRight($container, $watermark, $margin),
			self::Bottom => Position::bottom($container, $watermark, $margin),
			self::BottomLeft => Position::bottomLeft($container, $watermark, $margin),
			self::BottomRight => Position::bottomRight($container, $watermark, $margin),
		};
	}
}
