<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\operations\Saturation as BaseSaturation;
use mako\pixel\image\operations\traits\NormalizeTrait;
use Override;

/**
 * {@inheritDoc}
 */
class Saturation extends BaseSaturation
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

		$level = $this->normalizeLevel($this->level);

		$saturation = 100 + $level;

		$imageResource->modulateImage(100, $saturation, 100);
	}
}
