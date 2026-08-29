<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

/**
 * Flips the image.
 */
abstract class Flip implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected FlipDirection $direction = FlipDirection::Horizontal
	) {
	}
}
