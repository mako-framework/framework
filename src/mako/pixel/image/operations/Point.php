<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

/**
 * Represents a 2D coordinate.
 */
final readonly class Point
{
	/**
	 * Constructor.
	 */
	public function __construct(
		public int $x,
		public int $y
	) {
	}
}
