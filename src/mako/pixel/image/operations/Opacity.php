<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

/**
 * Adjusts the opacity of the image.
 */
abstract class Opacity implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected int $opacity
	) {
	}
}
