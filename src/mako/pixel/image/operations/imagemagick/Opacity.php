<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\operations\Opacity as OpacityOperation;
use mako\pixel\image\operations\traits\NormalizeTrait;
use Override;

/**
 * {@inheritDoc}
 */
class Opacity extends OpacityOperation
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
		$imageResource->evaluateImage(
			Imagick::EVALUATE_MULTIPLY,
			($this->normalizePercent($this->opacity) / 100),
			Imagick::CHANNEL_ALPHA
		);
	}
}
