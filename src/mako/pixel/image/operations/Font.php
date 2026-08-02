<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use mako\pixel\image\Color;

/**
 * Represents a font.
 */
final readonly class Font
{
	/**
	 * Constructor.
	 */
	public function __construct(
		public string $path,
		public int $size,
		public Color $color = new Color(0, 0, 0)
	) {
	}
}
