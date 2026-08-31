<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use mako\pixel\image\Color;

/**
 * Represents a font.
 *
 * The name is the font family name (e.g. "DejaVu Sans") and the size is
 * specified in pixels. Text rendering may produce slightly different
 * results between drivers as they use different rasterizers.
 */
final readonly class Font
{
	/**
	 * Constructor.
	 */
	public function __construct(
		public string $name,
		public string $path,
		public int $size,
		public Color $color = new Color(0, 0, 0)
	) {
	}
}
