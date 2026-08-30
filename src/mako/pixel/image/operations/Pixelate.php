<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use InvalidArgumentException;

/**
 * Pixelates the image.
 */
abstract class Pixelate implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected int $pixelSize = 10
	) {
		if ($pixelSize <= 1) {
			throw new InvalidArgumentException('Pixel size must be greater than 1.');
		}
	}
}
