<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use Deprecated;

/**
 * Flip.
 */
enum Flip
{
	/* Start compatibility */
	#[Deprecated('Use Flip::Horizontal instead', 'Mako 12.2.0')]
	public const HORIZONTAL = self::Horizontal;
	#[Deprecated('Use Flip::Vertical instead', 'Mako 12.2.0')]
	public const VERTICAL = self::Vertical;
	/* End compatibility */

	/**
	 * Flip horizontally.
	 */
	case Horizontal;

	/**
	 * Flip vertically.
	 */
	case Vertical;
}
