<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\inspectors;

use mako\pixel\image\Color;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\traits\PixelValidationTrait;

/**
 * Returns the color of the specified pixel.
 *
 * @implements InspectorInterface<Color>
 */
abstract class ColorAt implements InspectorInterface
{
	use PixelValidationTrait;

	/**
	 * Constructor.
	 */
	final public function __construct(
		protected Point $pixel
	) {
	}
}
