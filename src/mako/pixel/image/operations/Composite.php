<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use mako\pixel\image\geometry\Point;
use mako\pixel\image\ImageInterface;

/**
 * Composites an image onto the image at the specified position.
 */
abstract class Composite implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected ImageInterface $image,
		protected Point $position = new Point(0, 0)
	) {
	}
}
