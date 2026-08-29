<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\inspectors;

use mako\pixel\image\Color;

/**
 * Extracts the dominant colors from an image.
 *
 * Similar colors are grouped together to avoid returning multiple
 * variations of the same color.
 *
 * @implements InspectorInterface<array<int, Color>>
 */
abstract class TopColors implements InspectorInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected int $limit = 5,
		protected bool $ignoreTransparent = true,
	) {
	}
}
