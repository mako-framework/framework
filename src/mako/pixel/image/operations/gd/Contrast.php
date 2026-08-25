<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\traits\NormalizeTrait;
use Override;

use function imagefilter;

/**
 * Adjusts the image contrast.
 */
class Contrast implements OperationInterface
{
	use NormalizeTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $level = 0
	) {
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->level === 0) {
			return;
		}

		imagefilter(
			$imageResource,
			IMG_FILTER_CONTRAST,
			-$this->normalizeLevel($this->level) // negative = more contrast
		);
	}
}
