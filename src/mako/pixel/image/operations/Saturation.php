<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

/**
 * Adjusts the color saturation.
 */
abstract class Saturation implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected int $level = 0
	) {
	}
}
