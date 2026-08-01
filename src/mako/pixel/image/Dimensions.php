<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image;

/**
 * Dimensions in pixels.
 */
readonly class Dimensions
{
	/**
	 * Constructor.
	 */
	public function __construct(
		public int $width,
		public int $height
	) {
	}
}
