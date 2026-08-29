<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

/**
 * Adjusts the image color temperature.
 */
abstract class Temperature implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected int $level = 0
	) {
	}
}
