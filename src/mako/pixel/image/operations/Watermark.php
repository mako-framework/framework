<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use mako\pixel\image\ImageInterface;

/**
 * Adds a watermark to the image.
 */
abstract class Watermark implements OperationInterface
{
	/**
	 * Constructor.
	 */
	final public function __construct(
		protected ImageInterface|string $image,
		protected WatermarkPosition $position = WatermarkPosition::BottomRight,
		protected int $opacity = 100,
		protected int $margin = 0
	) {
		if ($image instanceof ($this->getImageClass()) === false) {
			$this->image = new ($this->getImageClass())($image);
		}
	}

	/**
	 * Returns the image implementation class name.
	 *
	 * @return class-string<ImageInterface>
	 */
	abstract protected function getImageClass(): string;
}
