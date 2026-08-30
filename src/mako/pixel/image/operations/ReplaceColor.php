<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use mako\pixel\image\Color;

use function max;
use function min;

/**
 * Replaces pixels matching a specified color with another color.
 *
 * The tolerance ranges from 0 (only exact matches are replaced) to 100
 * (all colors match). Values outside this range will be clamped.
 *
 * Setting the invert match flag to true inverts the matching, replacing
 * all pixels that do NOT match the specified color.
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
		$this->tolerance = max(0, min(100, $tolerance));
	}
}
