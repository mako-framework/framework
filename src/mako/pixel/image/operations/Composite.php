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
 *
 * @template T of ImageInterface
 */
abstract class Composite implements OperationInterface
{
	/**
	 * Image.
	 *
	 * @var T
	 */
	protected ImageInterface $image;

	/**
	 * Constructor.
	 *
	 * @param T $image
	 */
	final public function __construct(
		ImageInterface $image,
		protected Point $position = new Point(0, 0)
	) {
		$this->image = $image;
	}
}
