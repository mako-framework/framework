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

use function abs;

/**
 * Adjusts the image color temperature.
 */
class Temperature implements OperationInterface
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
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource, string $imagePath): void
	{
		if ($this->level == 0) {
			return;
		}

		$level = $this->normalizeLevel($this->level);

		$shift = $level * 0.0022;

		if ($shift > 0) {
			$imageResource->evaluateImage(Imagick::EVALUATE_MULTIPLY, 1 + $shift, Imagick::CHANNEL_RED);
			$imageResource->evaluateImage(Imagick::EVALUATE_MULTIPLY, 1 - $shift, Imagick::CHANNEL_BLUE);
		}
		elseif ($shift < 0) {
			$imageResource->evaluateImage(Imagick::EVALUATE_MULTIPLY, 1 + abs($shift), Imagick::CHANNEL_BLUE);
			$imageResource->evaluateImage(Imagick::EVALUATE_MULTIPLY, 1 - abs($shift), Imagick::CHANNEL_RED);
		}
	}
}
