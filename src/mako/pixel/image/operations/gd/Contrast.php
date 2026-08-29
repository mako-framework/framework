<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\Contrast as ContrastOperation;
use mako\pixel\image\operations\traits\NormalizeTrait;
use Override;

use function imagefilter;

/**
 * {@inheritDoc}
 */
class Contrast extends ContrastOperation
{
	use NormalizeTrait;

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
