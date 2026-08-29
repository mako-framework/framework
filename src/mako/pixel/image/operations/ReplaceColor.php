<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use mako\pixel\image\Color;

/**
 * Replaces pixels matching a specified color with another color.
 */
abstract class ReplaceColor implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected Color $from,
		protected Color $to,
		protected int $tolerance = 0,
		protected bool $invertMatch = false
	) {
	}
}
