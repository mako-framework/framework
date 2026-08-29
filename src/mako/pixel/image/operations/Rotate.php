<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

/**
 * Rotates the image.
 */
abstract class Rotate implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected int $degrees = 0
	) {
	}
}
