<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use InvalidArgumentException;

/**
 * Adjusts the gamma level of the image.
 *
 * The gamma value must be greater than 0. Values below 1.0 darken the image,
 * a value of 1.0 leaves it unchanged, and values above 1.0 brighten it.
 */
abstract class Gamma implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected float $gamma
	) {
		if ($gamma <= 0) {
			throw new InvalidArgumentException('Gamma must be greater than 0.');
		}
	}
}
