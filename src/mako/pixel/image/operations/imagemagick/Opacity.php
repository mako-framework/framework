<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\traits\NormalizeTrait;
use Override;

/**
 * Adjusts the opacity of the image.
 */
class Opacity implements OperationInterface
{
	use NormalizeTrait;

	public function __construct(
		protected int $opacity
	) {
	}

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
