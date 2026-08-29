<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use InvalidArgumentException;

/**
 * Scales the image.
 */
abstract class Scale implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected int $percent {
			set(int $value) {
				if ($value <= 0) {
					throw new InvalidArgumentException('Scale percentage must be greater than zero.');
				}
				$this->percent = $value;
			}
		},
	) {
	}
}
