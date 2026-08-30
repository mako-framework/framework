<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use ImagickPixel;
use mako\pixel\image\operations\ReplaceColor as ReplaceColorOperation;
use Override;

/**
 * {@inheritDoc}
 */
class ReplaceColor extends ReplaceColorOperation
{
	/**
	 * Converts the tolerance percentage to the active Imagick quantum range.
	 */
	protected function normalizeTolerance(): float
	{
		$quantumRange = (float) Imagick::getQuantumRange()['quantumRangeLong'];

		return ($this->tolerance / 100) * $quantumRange;
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
