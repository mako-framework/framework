<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use Deprecated;

/**
 * Aspect ratio.
 */
enum AspectRatio
{
	/* Start compatibility */
	#[Deprecated('use AspectRatio::Ignore instead', 'Mako 12.2.0')]
	public const IGNORE = self::Ignore;
	#[Deprecated('use AspectRatio::Auto instead', 'Mako 12.2.0')]
	public const AUTO = self::Auto;
	#[Deprecated('use AspectRatio::Width instead', 'Mako 12.2.0')]
	public const WIDTH = self::Width;
	#[Deprecated('use AspectRatio::Height instead', 'Mako 12.2.0')]
	public const HEIGHT = self::Height;
	/* End compatibility */

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
