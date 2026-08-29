<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

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
	}
}
