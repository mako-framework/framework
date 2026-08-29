<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use ImagickPixel;
use mako\pixel\image\operations\ReplaceColor as BaseReplaceColor;
use Override;

use function max;
use function min;

/**
 * {@inheritDoc}
 */
class ReplaceColor extends BaseReplaceColor
{
	/**
	 * Converts the tolerance percentage to the active Imagick quantum range.
	 */
	protected function normalizeTolerance(): float
	{
		$tolerance = max(0, min(100, $this->tolerance));

		$quantumRange = (float) Imagick::getQuantumRange()['quantumRangeLong'];

		return ($tolerance / 100) * $quantumRange;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$imageResource->opaquePaintImage(
			new ImagickPixel($this->from->toHexaString()),
			new ImagickPixel($this->to->toHexaString()),
			$this->normalizeTolerance(),
			$this->invertMatch
		);
	}
}
