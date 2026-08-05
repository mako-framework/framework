<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use ImagickPixel;
use mako\pixel\image\Color;
use mako\pixel\image\operations\OperationInterface;
use Override;

use function max;
use function min;

/**
 * Replaces pixels matching a specified color with another color.
 */
class ReplaceColor implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Color $from,
		protected Color $to,
		protected int $tolerance = 0,
		protected bool $invertMatch = false
	) {
	}

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
