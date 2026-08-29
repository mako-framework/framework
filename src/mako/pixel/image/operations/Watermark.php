<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use mako\pixel\image\ImageInterface;

/**
 * Adds a watermark to the image.
 *
 * @template T of ImageInterface
 */
abstract class Watermark implements OperationInterface
{
	/**
	 * Image.
	 *
	 * @var T $image
	 */
	protected ImageInterface $image;

	/**
	 * Constructor.
	 *
	 * @param T $image
	 */
	final public function __construct(
		ImageInterface $image,
		protected WatermarkPosition $position = WatermarkPosition::BottomRight,
		protected int $opacity = 100,
		protected int $margin = 0
	) {
		$this->image = $image;
	}
}
