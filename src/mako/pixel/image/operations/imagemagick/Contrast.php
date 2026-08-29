<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\operations\Contrast as BaseContrast;
use mako\pixel\image\operations\traits\NormalizeTrait;
use Override;

/**
 * {@inheritDoc}
 */
class Contrast extends BaseContrast
{
	use NormalizeTrait;

	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->level === 0) {
			return;
		}

		$imageResource->brightnessContrastImage(0, $this->normalizeLevel($this->level));
	}
}
