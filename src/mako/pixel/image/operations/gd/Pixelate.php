<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use mako\pixel\image\operations\OperationInterface;
use Override;

use function imagefilter;

/**
 * Pixelates the image.
 */
class Pixelate implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $pixelSize = 10
	) {
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param \GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		imagefilter($imageResource, IMG_FILTER_PIXELATE, $this->pixelSize, IMG_FILTER_PIXELATE);
	}
}
