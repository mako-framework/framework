<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

/**
 * Adjusts the image brightness.
 */
abstract class Brightness implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected int $level = 0
	) {
	}
}
