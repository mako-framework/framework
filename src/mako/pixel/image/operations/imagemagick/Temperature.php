<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\operations\Temperature as TemperatureOperation;
use Override;

/**
 * {@inheritDoc}
 */
class Temperature extends TemperatureOperation
{
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

		$shift = $this->level * 0.0022;

		$imageResource->evaluateImage(Imagick::EVALUATE_MULTIPLY, 1 + $shift, Imagick::CHANNEL_RED);
		$imageResource->evaluateImage(Imagick::EVALUATE_MULTIPLY, 1 - $shift, Imagick::CHANNEL_BLUE);
	}
}
